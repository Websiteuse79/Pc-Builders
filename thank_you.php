<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You!</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #1a1a2e;
            color: #fff;
            overflow: hidden;
        }

        .thank-you-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            box-sizing: border-box;
        }

        @media (max-width: 768px) {
            .thank-you-container {
                padding: 2rem 1rem;
                max-width: 95vw;
            }
            .thank-you-container h1 {
                font-size: 2.5rem !important;
            }
            .thank-you-container p {
                font-size: 1.1rem !important;
            }
            .cta-button {
                padding: 0.75rem 2rem !important;
                font-size: 1rem !important;
            }
            svg {
                width: 4rem !important;
                height: 4rem !important;
            }
        }

        @media (max-width: 480px) {
            .thank-you-container {
                padding: 1rem 0.5rem;
                max-width: 100vw;
            }
            .thank-you-container h1 {
                font-size: 2rem !important;
            }
            .thank-you-container p {
                font-size: 1rem !important;
            }
            .cta-button {
                padding: 0.5rem 1rem !important;
                font-size: 0.95rem !important;
            }
            svg {
                width: 3rem !important;
                height: 3rem !important;
            }
        }

        .star {
            position: absolute;
            background-color: #fff;
            border-radius: 50%;
            animation: twinkle 1.5s infinite ease-in-out;
            z-index: 1;
        }
        
        @keyframes twinkle {
            0%, 100% { transform: scale(0.5); opacity: 0.2; }
            50% { transform: scale(1.2); opacity: 1; }
        }

        .confetti-piece {
            position: absolute;
            width: 10px;
            height: 10px;
            background-color: currentColor;
            opacity: 0;
            animation: fall linear forwards;
            z-index: 5;
        }

        @keyframes fall {
            0% { transform: translateY(-100vh) rotate(0deg); opacity: 1; }
            100% { transform: translateY(100vh) rotate(720deg); opacity: 0; }
        }

        .fade-in {
            animation: fadeIn 2s ease-in-out forwards;
        }
        
        @keyframes fadeIn {
            0% { opacity: 0; transform: translateY(20px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        .cta-button {
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
        }
    </style>
</head>
<body>

<div id="stars-container"></div>
<div id="confetti-container"></div>

<div class="thank-you-container px-6 py-12 md:px-12">
    <svg class="text-green-400 w-24 h-24 mb-6 fade-in" style="animation-delay: 0.5s;" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
    </svg>

    <h1 class="text-5xl md:text-7xl font-bold mb-4 fade-in" style="animation-delay: 1s;">Thank You!</h1>
    <p class="text-xl md:text-2xl text-gray-300 mb-8 max-w-2xl fade-in" style="animation-delay: 1.5s;">Your submission has been received successfully. We appreciate your time and effort.</p>
    
   <a href="index.php">
     <button onclick="window.location.reload()" class="cta-button bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-full fade-in" style="animation-delay: 2s;">
          Return to Home
      </button>
   </a> 
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const createStars = () => {
            const starsContainer = document.getElementById('stars-container');
            const numStars = 100;
            for (let i = 0; i < numStars; i++) {
                const star = document.createElement('div');
                star.className = 'star';
                star.style.width = star.style.height = `${Math.random() * 3 + 1}px`;
                star.style.left = `${Math.random() * 100}vw`;
                star.style.top = `${Math.random() * 100}vh`;
                star.style.animationDelay = `${Math.random() * 1.5}s`;
                starsContainer.appendChild(star);
            }
        };

        const createConfetti = () => {
            const confettiContainer = document.getElementById('confetti-container');
            const numConfetti = 50;
            const colors = ['#f44336', '#e91e63', '#9c27b0', '#673ab7', '#3f51b5', '#2196f3', '#03a9f4', '#00bcd4', '#009688', '#4caf50', '#8bc34a', '#cddc39', '#ffeb3b', '#ffc107', '#ff9800', '#ff5722', '#795548', '#9e9e9e', '#607d8b'];
            
            for (let i = 0; i < numConfetti; i++) {
                const confetti = document.createElement('div');
                confetti.className = 'confetti-piece';
                confetti.style.left = `${Math.random() * 100}vw`;
                confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.animationDuration = `${Math.random() * 3 + 2}s`;
                confetti.style.animationDelay = `${Math.random() * 0.5}s`;
                confettiContainer.appendChild(confetti);
            }
        };

        createStars();
        createConfetti();
    });
</script>

</body>
</html>
