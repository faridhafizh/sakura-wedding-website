<?php
// --- PHP LOGIC FOR RSVP PROCESSING ---
session_start(); // Optional: For displaying messages across page loads if not using AJAX

// --- Database Configuration ---
$dbHost = 'localhost';
$dbUser = 'root';     // <-- IMPORTANT: REPLACE WITH YOUR DB USER
$dbPass = '123';  // <-- IMPORTANT: REPLACE WITH YOUR DB PASSWORD
$dbName = 'wedding_db';      // <-- IMPORTANT: REPLACE WITH YOUR DB NAME

$rsvp_message = ''; // To store feedback messages for the user
$rsvp_success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_rsvp'])) {
    // --- Establish Database Connection ---
    $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

    if ($conn->connect_error) {
        error_log("Database Connection Error: " . $conn->connect_error);
        $rsvp_message = 'Error: Could not connect to the database. Please try again later.';
    } else {
        // Get and sanitize input data
        $name = isset($_POST['name']) ? trim($conn->real_escape_string($_POST['name'])) : '';
        $email = isset($_POST['email']) ? trim($conn->real_escape_string($_POST['email'])) : '';
        $attending = isset($_POST['attending']) ? $conn->real_escape_string($_POST['attending']) : '';
        $guests = isset($_POST['guests']) ? (int)$_POST['guests'] : 1;
        $message_text = isset($_POST['message']) ? trim($conn->real_escape_string($_POST['message'])) : null; // Renamed to avoid conflict with $rsvp_message
        $ip_address = $_SERVER['REMOTE_ADDR'];

        // --- Server-Side Validation ---
        if (empty($name)) {
            $rsvp_message = 'Name is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $rsvp_message = 'Invalid email format.';
        } elseif (empty($attending) || !in_array($attending, ['yes', 'no'])) {
            $rsvp_message = 'Please specify if you are attending.';
        } elseif ($guests < 1 || $guests > 10) { // Adjust max guests
            $rsvp_message = 'Invalid number of guests.';
        } else {
            // --- Prepare and Bind Statement (to prevent SQL injection) ---
            $stmt = $conn->prepare("INSERT INTO rsvps (name, email, attending, guests, message, ip_address) VALUES (?, ?, ?, ?, ?, ?)");

            if ($stmt === false) {
                error_log("Prepare failed: (" . $conn->errno . ") " . $conn->error);
                $rsvp_message = 'An error occurred preparing your RSVP.';
            } else {
                $stmt->bind_param("sssiss", $name, $email, $attending, $guests, $message_text, $ip_address);

                if ($stmt->execute()) {
                    $rsvp_success = true;
                    $rsvp_message = 'Thank you, ' . htmlspecialchars($name) . '! Your RSVP has been recorded.';
                    // Optionally clear form fields or redirect to avoid resubmission on refresh
                    // For single file, clearing can be tricky without JS.
                    // A common pattern is to redirect:
                    // header("Location: " . $_SERVER['PHP_SELF'] . "?rsvp_status=success");
                    // exit;
                    // Then you'd check for $_GET['rsvp_status'] at the top.
                    // For simplicity with current JS approach, we just set message.
                } else {
                    if ($conn->errno == 1062) {
                        $rsvp_message = 'This email address has already RSVPd. If you need to make changes, please contact us directly.';
                    } else {
                        error_log("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
                        $rsvp_message = 'An error occurred while saving your RSVP.';
                    }
                }
                $stmt->close();
            }
        }
        $conn->close();
    }
    // If using AJAX, this PHP block would typically end with json_encode and exit.
    // Since we are doing a full page postback for this example, $rsvp_message will be used below in HTML.
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Haruka & Yuto - A Sakura Wedding</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

    <!-- AnimeJS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>

    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #FFFBF7;
            color: #5D504A;
            overflow-x: hidden;
        }
        .font-playfair { font-family: 'Playfair Display', serif; }
        .font-montserrat { font-family: 'Montserrat', sans-serif; }
        .section-title { position: relative; padding-bottom: 0.75rem; margin-bottom: 2rem; color: #D98695; }
        .section-title::after { content: ''; position: absolute; left: 50%; bottom: 0; transform: translateX(-50%); width: 70px; height: 2px; background-color: #F2C2C2; }
        .btn-primary { background-color: #D98695; color: white; transition: background-color 0.3s ease, transform 0.3s ease; padding: 0.75rem 1.5rem; border-radius: 0.5rem; font-weight: 500; }
        .btn-primary:hover { background-color: #C76C7E; transform: translateY(-2px); }
        #rsvp-form input, #rsvp-form select, #rsvp-form textarea { border: 1px solid #E5D9D4; border-radius: 0.375rem; padding: 0.75rem 1rem; transition: border-color 0.3s ease, box-shadow 0.3s ease; background-color: #FFFCFA; width: 100%; }
        #rsvp-form input:focus, #rsvp-form select:focus, #rsvp-form textarea:focus { border-color: #D98695; box-shadow: 0 0 0 3px rgba(217, 134, 149, 0.2); outline: none; }
        #map-container { opacity: 0; max-height: 0; overflow: hidden; transition: opacity 0.5s ease-in-out, max-height 0.5s ease-in-out; }
        #map-container.visible { opacity: 1; max-height: 400px; }
        .sakura-leaf { position: absolute; width: 15px; height: 15px; background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="%23F2C2C2" d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>'); background-size: contain; background-repeat: no-repeat; opacity: 0.5; animation: fall linear infinite; z-index: 0; }
        @keyframes fall {
            0% { transform: translateY(-5vh) translateX(0vw) rotate(0deg); opacity: 0.6; }
            50% { transform: translateY(50vh) translateX(calc(5vw * (0.5 - Math.random()))) rotate(180deg); opacity: 0.4; }
            100% { transform: translateY(105vh) translateX(calc(10vw * (0.5 - Math.random()))) rotate(360deg); opacity: 0; }
        }
        .slideshow { position: relative; max-width: 100%; margin: auto; overflow: hidden; border-radius: 0.5rem; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }
        .slide { display: none; width: 100%; height: auto; animation: fade 1.5s ease-in-out; }
        @keyframes fade { from {opacity: .6} to {opacity: 1} }
        .slide.active { display: block; }
        .music-player { position: fixed; bottom: 20px; right: 20px; background: rgba(255, 251, 247, 0.9); padding: 10px 15px; border-radius: 0.5rem; box-shadow: 0 4px 12px rgba(93, 80, 74, 0.15); z-index: 1000; }
        .music-player audio { display: block; max-width: 250px; }
        .wave-effect { position: relative; overflow: hidden; }
        .wave { position: absolute; background: rgba(217, 134, 149, 0.3); border-radius: 50%; transform: scale(0); pointer-events: none; animation: wave-anim 0.6s ease-out; }
        @keyframes wave-anim { to { transform: scale(3); opacity: 0; } }
        .fade-in-up { opacity: 0; transform: translateY(20px); }
        .rsvp-feedback { margin-top: 1rem; padding: 0.75rem; border-radius: 0.375rem; text-align: center; }
        .rsvp-feedback.success { background-color: #E6FFFA; border: 1px solid #A7F3D0; color: #047857; }
        .rsvp-feedback.error { background-color: #FFF5F5; border: 1px solid #FCA5A5; color: #B91C1C; }
    </style>
</head>
<body class="antialiased">

    <div id="sakura-container"></div>

    <header class="text-center py-10 md:py-16 bg-gradient-to-b from-rose-50 to-transparent">
        <div class="container mx-auto px-4">
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-playfair text-rose-700 mb-3">Haruka & Yuto</h1>
            <p class="text-lg md:text-xl font-montserrat text-gray-600">Join us for a celebration of love under the sakura</p>
        </div>
    </header>

    <main class="container mx-auto px-4 space-y-16 md:space-y-24 pb-16">
        <section class="hero text-center">
            <img src="https://images.unsplash.com/photo-1522861904550-063491193019?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTF8fHNhS3VyYSUyMHdlZGRpbmd8ZW58MHx8MHx8fDA%3D&auto=format&fit=crop&w=1000&q=80"
                 alt="Couple under Sakura Trees"
                 class="mx-auto w-full max-w-2xl h-auto rounded-lg shadow-xl" id="heroImage">
            <p class="mt-6 text-lg text-gray-700 font-light italic">"Two souls, one heart, blooming forever."</p>
        </section>

        <section class="countdown-section text-center py-12 bg-rose-50 rounded-xl shadow-lg">
            <h2 class="text-3xl md:text-4xl font-playfair section-title">The Day Approaches</h2>
            <div id="countdown" class="text-3xl md:text-4xl lg:text-5xl font-montserrat font-semibold text-rose-600 tracking-wider space-x-2 md:space-x-4">
            </div>
            <p class="mt-4 text-gray-600">until we say "I Do"</p>
        </section>

        <section class="details-section text-center">
            <h2 class="text-3xl md:text-4xl font-playfair section-title">Wedding Details</h2>
            <div class="max-w-xl mx-auto space-y-3 text-lg text-gray-700">
                <p><strong class="font-semibold text-rose-700">Date:</strong> December 31, 2024</p>
                <p><strong class="font-semibold text-rose-700">Time:</strong> 2:00 PM</p>
                <p><strong class="font-semibold text-rose-700">Location:</strong> Serene Garden Pavilion, Tokyo, Japan</p>
            </div>
            <button id="map-toggle" class="mt-8 btn-primary wave-effect">
                View Location Map
            </button>
            <div id="map-container" class="mt-6 max-w-3xl mx-auto rounded-lg overflow-hidden shadow-md">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3240.831834774937!2d139.76487231523438!3d35.6812362801925!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x60188bfd823e8d2d%3A0x7b5c7b5c7b5c7b5c!2sTokyo%2C%20Japan!5e0!3m2!1sen!2sus!4v1625779999999!5m2!1sen!2sus"
                        width="100%" height="350" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
            </div>
        </section>

        <section class="gallery-section text-center">
            <h2 class="text-3xl md:text-4xl font-playfair section-title">Our Moments</h2>
            <div class="slideshow max-w-3xl mx-auto">
                <img src="https://images.unsplash.com/photo-1588339403651-d120f755def0?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Nnx8c2FrdXJhJTIwd2VkZGluZ3xlbnwwfHwwfHx8MA%3D&auto=format&fit=crop&w=800&q=60" alt="Gallery Image 1" class="slide active">
                <img src="https://images.unsplash.com/photo-1550081690-7a6e59083dbd?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTB8fHNhS3VyYSUyMHdlZGRpbmd8ZW58MHx8MHx8fDA%3D&auto=format&fit=crop&w=800&q=60" alt="Gallery Image 2" class="slide">
                <img src="https://images.unsplash.com/photo-1611957737531-0665094610e9?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8M3x8c2FrdXJhJTIwd2VkZGluZ3xlbnwwfHwwfHx8MA%3D&auto=format&fit=crop&w=800&q=60" alt="Gallery Image 3" class="slide">
                <img src="https://images.unsplash.com/photo-1522080096074-029a192d4717?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTV8fHNhS3VyYSUyMHdlZGRpbmd8ZW58MHx8MHx8fDA%3D&auto=format&fit=crop&w=800&q=60" alt="Gallery Image 4" class="slide">
            </div>
        </section>

        <section class="rsvp-section text-center">
            <h2 class="text-3xl md:text-4xl font-playfair section-title">Will You Join Us?</h2>

            <?php if (!empty($rsvp_message)): ?>
                <div id="rsvp-feedback-server" class="rsvp-feedback <?php echo $rsvp_success ? 'success' : 'error'; ?>">
                    <?php echo htmlspecialchars($rsvp_message); ?>
                </div>
            <?php endif; ?>
            <div id="rsvp-feedback-js" class="rsvp-feedback" style="display:none;"></div>


            <form id="rsvp-form" class="max-w-lg mx-auto space-y-6" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>#rsvp-section">
                 <input type="hidden" name="submit_rsvp" value="1"> <!-- Identifier for PHP processing block -->
                <div>
                    <label for="name" class="block text-md font-medium text-gray-700 text-left mb-1">Full Name</label>
                    <input type="text" id="name" name="name" required value="<?php echo isset($_POST['name']) && !$rsvp_success ? htmlspecialchars($_POST['name']) : ''; ?>">
                </div>
                <div>
                    <label for="email" class="block text-md font-medium text-gray-700 text-left mb-1">Email Address</label>
                    <input type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) && !$rsvp_success ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                <div>
                    <label for="attending" class="block text-md font-medium text-gray-700 text-left mb-1">Will you be attending?</label>
                    <select id="attending" name="attending" required>
                        <option value="" disabled <?php echo !isset($_POST['attending']) && !$rsvp_success ? 'selected' : ''; ?>>Please select...</option>
                        <option value="yes" <?php echo isset($_POST['attending']) && $_POST['attending'] == 'yes' && !$rsvp_success ? 'selected' : ''; ?>>Yes, with pleasure!</option>
                        <option value="no" <?php echo isset($_POST['attending']) && $_POST['attending'] == 'no' && !$rsvp_success ? 'selected' : ''; ?>>Regretfully, no.</option>
                    </select>
                </div>
                 <div>
                    <label for="guests" class="block text-md font-medium text-gray-700 text-left mb-1">Number of Guests (including yourself)</label>
                    <select id="guests" name="guests">
                        <option value="1" <?php echo isset($_POST['guests']) && $_POST['guests'] == '1' && !$rsvp_success ? 'selected' : ''; ?>>1</option>
                        <option value="2" <?php echo isset($_POST['guests']) && $_POST['guests'] == '2' && !$rsvp_success ? 'selected' : ''; ?>>2</option>
                        <!-- Add more if needed -->
                    </select>
                </div>
                <div>
                    <label for="message" class="block text-md font-medium text-gray-700 text-left mb-1">Message (Optional)</label>
                    <textarea id="message" name="message" rows="3"><?php echo isset($_POST['message']) && !$rsvp_success ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                </div>
                <button type="submit" class="w-full btn-primary wave-effect text-lg py-3">Send RSVP</button>
            </form>
        </section>
    </main>

    <div class="music-player">
        <audio controls loop>
            <source src="wedding-music.mp3" type="audio/mpeg">
            Your browser does not support the audio element.
        </audio>
    </div>

    <footer class="text-center py-8 mt-16 border-t border-rose-100">
        <p class="text-gray-600">© <span id="currentYear"></span> Haruka & Yuto. All our love.</p>
    </footer>

    <script>
    'use strict';

    function createWaveEffect(event, element) {
        const wave = document.createElement('span');
        wave.classList.add('wave');
        const rect = element.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        wave.style.width = wave.style.height = `${size}px`;
        wave.style.left = `${event.clientX - rect.left - size / 2}px`;
        wave.style.top = `${event.clientY - rect.top - size / 2}px`;
        element.appendChild(wave);
        setTimeout(() => wave.remove(), 600);
    }

    function createSakuraLeaves() {
        const leafCount = 20;
        const container = document.getElementById('sakura-container') || document.body;
        for (let i = 0; i < leafCount; i++) {
            const leaf = document.createElement('div');
            leaf.classList.add('sakura-leaf');
            const baseDuration = 8;
            const randomDuration = Math.random() * 7;
            const animationDuration = baseDuration + randomDuration;
            const delay = Math.random() * 10;
            leaf.style.left = `${Math.random() * 100}%`;
            leaf.style.animationDuration = `${animationDuration}s`;
            leaf.style.animationDelay = `${delay}s`;
            const size = 10 + Math.random() * 10;
            leaf.style.width = `${size}px`;
            leaf.style.height = `${size}px`;
            container.appendChild(leaf);
        }
    }

    function startSlideshow() {
        const slides = document.querySelectorAll('.slide');
        if (slides.length === 0) return;
        let currentSlide = 0;
        function showSlide(index) {
            slides.forEach((slide, i) => slide.classList.toggle('active', i === index));
        }
        function nextSlide() {
            currentSlide = (currentSlide + 1) % slides.length;
            showSlide(currentSlide);
        }
        showSlide(currentSlide);
        setInterval(nextSlide, 4000);
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('currentYear').textContent = new Date().getFullYear();
        createSakuraLeaves();
        startSlideshow();

        const sections = document.querySelectorAll('main > section, header > div');
        sections.forEach((section, index) => {
            section.classList.add('fade-in-up');
            anime({
                targets: section,
                translateY: [20, 0],
                opacity: [0, 1],
                duration: 800,
                delay: index * 150,
                easing: 'easeOutExpo'
            });
        });

        function updateCountdown() {
            const weddingDate = new Date('December 31, 2024 14:00:00').getTime(); // UPDATE THIS
            const now = new Date().getTime();
            const distance = weddingDate - now;
            const countdownElement = document.getElementById('countdown');
            if (!countdownElement) return;

            if (distance < 0) {
                clearInterval(countdownInterval);
                countdownElement.innerHTML = "<span class='text-2xl'>The Celebration Has Begun!</span>";
                return;
            }
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            countdownElement.innerHTML = `
                <span class="inline-block"><span class="block text-5xl">${String(days).padStart(2, '0')}</span><span class="block text-xs uppercase tracking-widest">Days</span></span>
                <span class="inline-block"><span class="block text-5xl">${String(hours).padStart(2, '0')}</span><span class="block text-xs uppercase tracking-widest">Hours</span></span>
                <span class="inline-block"><span class="block text-5xl">${String(minutes).padStart(2, '0')}</span><span class="block text-xs uppercase tracking-widest">Minutes</span></span>
                <span class="inline-block"><span class="block text-5xl">${String(seconds).padStart(2, '0')}</span><span class="block text-xs uppercase tracking-widest">Seconds</span></span>
            `;
        }
        const countdownInterval = setInterval(updateCountdown, 1000);
        updateCountdown();

        const mapToggle = document.getElementById('map-toggle');
        const mapContainer = document.getElementById('map-container');
        if (mapToggle && mapContainer) {
            mapToggle.addEventListener('click', function(event) {
                createWaveEffect(event, mapToggle);
                mapContainer.classList.toggle('visible');
                mapToggle.textContent = mapContainer.classList.contains('visible') ? 'Hide Location Map' : 'View Location Map';
            });
        }

        // RSVP Form AJAX Submission (Optional, but recommended for better UX)
        const rsvpForm = document.getElementById('rsvp-form');
        const rsvpFeedbackJsDiv = document.getElementById('rsvp-feedback-js');
        const rsvpFeedbackServerDiv = document.getElementById('rsvp-feedback-server');


        if (rsvpForm && typeof fetch !== 'undefined') { // Check if fetch is supported
            rsvpForm.addEventListener('submit', function(e) {
                e.preventDefault(); // Prevent default synchronous form submission

                const formData = new FormData(rsvpForm);
                const submitButton = rsvpForm.querySelector('button[type="submit"]');
                const originalButtonText = submitButton.innerHTML;

                submitButton.innerHTML = 'Sending...';
                submitButton.disabled = true;
                rsvpFeedbackJsDiv.textContent = '';
                rsvpFeedbackJsDiv.className = 'rsvp-feedback'; // Reset classes
                rsvpFeedbackJsDiv.style.display = 'none';
                if(rsvpFeedbackServerDiv) rsvpFeedbackServerDiv.style.display = 'none'; // Hide server message if JS is active

                fetch('<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) { // Check for HTTP errors (4xx, 5xx)
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    // Try to parse as JSON first, if it fails, it might be HTML (error from PHP without JSON content type)
                    return response.text().then(text => {
                        try {
                            return JSON.parse(text);
                        } catch (err) {
                            // If parsing fails, it might be an unexpected HTML error page from PHP
                            // Log the raw text for debugging and throw a generic error
                            console.error("Failed to parse JSON response. Raw response:", text);
                            throw new Error("Received non-JSON response from server.");
                        }
                    });
                })
                .then(data => { // This data should now be the JSON from PHP if successful
                    rsvpFeedbackJsDiv.textContent = data.message || 'Processing complete.';
                    if (data.success) {
                        rsvpFeedbackJsDiv.classList.add('success');
                        rsvpForm.reset();
                    } else {
                        rsvpFeedbackJsDiv.classList.add('error');
                    }
                    rsvpFeedbackJsDiv.style.display = 'block';
                })
                .catch(error => {
                    console.error('RSVP Submission Error:', error);
                    rsvpFeedbackJsDiv.textContent = 'An error occurred. Please try again. (' + error.message + ')';
                    rsvpFeedbackJsDiv.classList.add('error');
                    rsvpFeedbackJsDiv.style.display = 'block';
                })
                .finally(() => {
                    submitButton.innerHTML = originalButtonText;
                    submitButton.disabled = false;
                });
            });
        } else if (rsvpForm) {
            // Fallback for browsers without fetch or if JS is disabled (PHP part at top will handle)
            console.log("Fetch API not supported or JS disabled for RSVP form, relying on standard form submission.");
        }

        // If there's a server-side message (e.g., after a non-JS submission or error), make sure it's visible
        if (rsvpFeedbackServerDiv && rsvpFeedbackServerDiv.textContent.trim() !== '') {
            rsvpFeedbackServerDiv.style.display = 'block';
            // Scroll to the RSVP section if a message is present
            const rsvpSection = document.getElementById('rsvp-section');
            if (rsvpSection) {
                // rsvpSection.scrollIntoView({ behavior: 'smooth' });
            }
        }


        document.querySelectorAll('.wave-effect').forEach(element => {
            element.addEventListener('click', function(event) {
                createWaveEffect(event, element);
            });
        });
    });
    </script>
</body>
</html>