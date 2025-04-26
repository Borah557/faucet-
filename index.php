<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=1080, maximum-scale=1.0, user-scalable=yes">
  <meta name="description" content="A responsive TRX (TRON) faucet website with FaucetPay API integration. Earn 0.001 TRX every 5 minutes. Fast login and registration via FaucetPay email.">
  <title>TRX Faucet | Earn Free TRON - Responsive Design</title>
  <!-- Tailwind CSS v2.2.19 -->
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <!-- Font Awesome 6.5.2 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css">
  <!-- Google Fonts (Montserrat & Roboto) -->
  <link href="https://cdn.jsdelivr.net/npm/@fontsource/montserrat@3.3.1/latin.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/@fontsource/roboto@3.3.1/latin.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Montserrat', 'Roboto', Arial, sans-serif;
      background: linear-gradient(135deg, #18181b 0%, #23272f 100%);
      color: #fff;
      margin: 0;
      min-width: 320px;
      overflow-x: hidden;
    }
    .bg-glow {
      box-shadow: 0 0 30px #29b6f6, 0 4px 6px #101010;
    }
    .vector-bg {
      background: url('https://cryptologos.cc/logos/tron-trx-logo.svg?v=026') no-repeat right bottom;
      background-size: 160px;
    }
    @media (max-width: 640px) {
      .vector-bg {
        background-size: 90px;
      }
    }
    .claim-anim {
      transition: box-shadow 0.2s, transform 0.12s;
    }
    .claim-anim:active {
      transform: scale(0.96);
      box-shadow: 0 0 12px #11ffa5dd, 0 2px 3px #000a;
    }
    .fade-in {
      opacity: 0;
      animation: fadeIn 1s ease forwards;
    }
    @keyframes fadeIn {
      to { opacity: 1; }
    }
    /* For custom scrollbar (will show in browser, ignored in PDF) */
    ::-webkit-scrollbar {
      height: 8px;
      width: 8px;
      background: transparent;
    }
    ::-webkit-scrollbar-thumb {
      background: #252c38;
      border-radius: 4px;
    }
  </style>
</head>
<body class="fade-in">

  <div class="max-w-2xl mx-auto p-4 sm:p-8 md:p-12 vector-bg">
    <!-- Header with vector illustration -->
    <header class="flex flex-col sm:flex-row items-center justify-between mb-8">
      <div class="flex-1">
        <h1 class="text-3xl md:text-4xl font-extrabold text-cyan-400 flex items-center gap-3">
          <span><i class="fab fa-stripe-s fa-spin text-red-400"></i></span> 
          TRX Faucet
        </h1>
        <p class="text-xl mt-2 text-blue-200 font-light max-w-md">
          Earn <span class="font-bold text-green-300">0.001</span> TRX every <span class="font-bold">5 minutes</span>.<br>
          Login with your FaucetPay email address below to start claiming.<br>
        </p>
      </div>
      <img src="https://assets-global.website-files.com/60c1a26e42148e313e8c027b/62fbd0cb0e224437c30e82a2_tron.png" 
        alt="TRON Vector Art" class="w-44 h-44 mt-6 sm:mt-0 object-contain float-right rounded-lg shadow-lg bg-glow hidden sm:block" loading="lazy" draggable="false">
    </header>

    <!-- Login/Register Card -->
    <section id="auth-section" class="bg-gray-900 rounded-xl shadow-lg px-6 py-5 mb-8 flex flex-col space-y-4 bg-glow">
      <form id="auth-form" autocomplete="off" class="flex flex-col sm:flex-row items center justify-between space-y-4 sm:space-y-0 sm:space-x-3">
        <div class="flex-1">
          <label for="email" class="block uppercase text-xs font-bold text-blue-200 tracking-wide mb-1">FaucetPay Email</label>
          <input type="email" id="email" name="email" required placeholder="your@email.com"
            class="w-full px-4 py-2 rounded-md bg-gray-800 text-white focus:outline-none focus:ring-2 focus:ring-cyan-500"
            autocomplete="username">
        </div>
        <button type="submit"
          class="claim-anim flex-grow sm:flex-grow-0 mt-1 sm:mt-0 px-6 py-3 rounded-lg font-bold text-lg bg-gradient-to-r from-cyan-400 to-green-300 shadow-lg text-gray-900 hover:from-cyan-300 hover:to-green-200 hover:text-gray-800 transition-all duration-150"
          aria-label="Login with FaucetPay email">
          <i class="fas fa-sign-in-alt mr-2"></i>
          Login / Register
        </button>
      </form>
      <p id="auth-error" class="hidden mt-2 px-2 text-pink-300 text-sm font-semibold"></p>
    </section>

    <!-- Main Faucet Card (hidden at first) -->
    <section id="faucet-section" class="bg-gray-900 rounded-xl shadow-lg px-6 py-5 mb-8 space-y-3 flex flex-col items-center bg-glow hidden">
      <div class="w-full flex flex-col sm:flex-row sm:justify-between items-center sm:items-end mb-2">
        <div>
          <span class="text-lg text-cyan-200 font-bold">Welcome, <span id="user-email" class="text-green-300">user@email.com</span></span>
        </div>
        <button id="logout-btn" class="text-red-400 text-sm border border-red-500 px-3 py-1 rounded ml-0 sm:ml-2 hover:bg-red-500 hover:text-white transition-all font-semibold mt-2 sm:mt-0">
          <i class="fas fa-sign-out-alt"></i> Logout
        </button>
      </div>
      <div class="flex flex-col items-center justify-center w-full mb-2">
        <div class="flex items-center mb-1">
          <span class="text-lg font-bold text-yellow-200">Reward:</span>
          <span class="ml-2 text-xl font-extrabold text-green-300">0.001 <span class="text-red-400">TRX</span></span>
        </div>
        <div class="flex items-center gap-2 mb-2">
          <i class="fas fa-clock text-cyan-300"></i> 
          <span id="timer" class="font-mono text-lg text-cyan-100">Ready to claim!</span>
        </div>
        <button id="claim-btn"
          class="claim-anim px-7 py-3 mt-2 rounded-full text-lg font-bold bg-gradient-to-r from-pink-400 to-red-500 text-white tracking-wide shadow-xl border-2 border-transparent hover:from-pink-300 hover:to-red-400 hover:border-pink-200 transition flex items-center gap-2"
        >
          <i class="fas fa-hand-holding-usd mr-1"></i>
          Claim Now
        </button>
        <div id="claim-result" class="w-full text-center mt-3 text-base"></div>
      </div>
    </section>

    <!-- How It Works / Features -->
    <section class="mt-2 mb-8">
      <h2 class="text-2xl font-bold mb-2 text-pink-200"><i class="fas fa-question-circle mr-1"></i> How It Works</h2>
      <ul class="list-none pl-0 space-y-1 text-blue-200 text-base">
        <li class="flex items-center gap-2">
          <i class="fas fa-user-shield text-purple-400"></i>
          <span>Login/register with your <b>FaucetPay email</b> in one step. No extra passwords!</span>
        </li>
        <li class="flex items-center gap-2">
          <i class="fas fa-bolt text-yellow-300"></i>
          <span>Every <b>5 minutes</b>, click <span class="text-green-300 font-bold">Claim</span> to receive <span class="text-green-300 font-bold">0.001 TRX</span>.</span>
        </li>
        <li class="flex items-center gap-2">
          <i class="fas fa-wallet text-cyan-400"></i>
          <span>Rewards are sent via the <b>FaucetPay API</b> directly to your FaucetPay account.</span>
        </li>
        <li class="flex items-center gap-2">
          <i class="fas fa-mobile-alt text-green-300"></i>
          <span>Fully <b>responsive</b> design — works smoothly on all devices!</span>
        </li>
        <li class="flex items-center gap-2">
          <i class="fas fa-shield-alt text-pink-400"></i>
          <span>1 claim per account per 5 minutes to keep things fair.</span>
        </li>
      </ul>
    </section>

    <!-- Responsive TRON illustration + blockquote features -->
    <section class="flex flex-col items-center my-10">
      <img src="https://cdn.vectorstock.com/i/1000x1000/77/53/tron-cryptocurrency-coins-logo-icon-vector-23517753.webp"
           alt="TRX Crypto Art" class="w-32 h-32 mb-4 rounded-xl shadow-xl mx-auto" loading="lazy" draggable="false">
      <blockquote class="italic text-lg text-blue-300 px-4 py-3 border-l-4 border-cyan-400 bg-gray-800 rounded">
        “Power up your wallet with free TRX - fast, secure, unstoppable. Powered by FaucetPay API.”
      </blockquote>
    </section>

    <!-- Footer -->
    <footer class="text-center mt-10 mb-4 text-gray-400 text-base px-2">
      <div class="flex flex-col sm:flex-row justify-between items-center">
        <span>TRX Faucet &copy; 2024 | <i class="fab fa-github-alt"></i> YourProjectName</span>
        <span class="mt-2 sm:mt-0">Not affiliated with TRON or FaucetPay. For demo purposes.</span>
      </div>
    </footer>
  </div>

  <script>
    // Simple state persistence for mockup (localStorage)
    const faucetInterval = 5 * 60; // seconds (5 minutes)
    const claimAmountTRX = 0.001;

    const $ = (id) => document.getElementById(id);

    function formatHMS(sec) {
      const m = Math.floor(sec / 60);
      const s = sec % 60;
      return `${m}:${s.toString().padStart(2, '0')}`;
    }

    // --- Auth section ---
    function setAuthError(msg) {
      const el = $('auth-error');
      el.textContent = msg;
      el.classList.remove('hidden');
    }
    function clearAuthError() {
      const el = $('auth-error');
      el.innerHTML = '';
      el.classList.add('hidden');
    }

    // --- Faucet Login/Register ---
    $('auth-form').onsubmit = function(e) {
      e.preventDefault();
      clearAuthError();
      const email = $('email').value.trim().toLowerCase();
      if(!/^[\w\-\.]+@[\w\-]+\.[\w\-]+$/.test(email)) {
        setAuthError("Enter a valid FaucetPay email address.");
        return;
      }
      // Simulate API login/registration
      localStorage.setItem('trx_faucet_user_email', email);
      $('user-email').textContent = email;
      $('auth-section').classList.add('hidden');
      $('faucet-section').classList.remove('hidden');
      loadClaimState();
    };

    // --- Logout ---
    $('logout-btn').onclick = function() {
      localStorage.removeItem('trx_faucet_user_email');
      $('faucet-section').classList.add('hidden');
      $('auth-section').classList.remove('hidden');
      $('email').value = '';
      // PDF optimization: do not scroll or focus
    };

    // --- Claim Logic ---
    function getLastClaimInfo(email) {
      const claimInfo = localStorage.getItem('trx_claim_' + email);
      try {
        return claimInfo ? JSON.parse(claimInfo) : { t: 0 };
      } catch { return { t: 0 }; }
    }
    function setLastClaimInfo(email, unixTs) {
      localStorage.setItem('trx_claim_' + email, JSON.stringify({ t: unixTs }));
    }

    function loadClaimState() {
      const email = localStorage.getItem('trx_faucet_user_email');
      $('user-email').textContent = email;
      const claimBtn = $('claim-btn');
      const timerEl = $('timer');
      const claimResult = $('claim-result');
      let lastClaim = getLastClaimInfo(email).t || 0;
      let timeNow = Math.floor(Date.now() / 1000);
      let waitSec = (lastClaim + faucetInterval) - timeNow;
      function updateUI() {
        timeNow = Math.floor(Date.now() / 1000);
        waitSec = (lastClaim + faucetInterval) - timeNow;
        if(waitSec <= 0) {
          claimBtn.disabled = false;
          claimBtn.classList.remove('opacity-50', 'cursor-not-allowed');
          timerEl.textContent = "Ready to claim!";
        } else {
          claimBtn.disabled = true;
          claimBtn.classList.add('opacity-50', 'cursor-not-allowed');
          timerEl.textContent = "Next claim in: " + formatHMS(waitSec);
        }
      }
      updateUI();
      let interval = setInterval(updateUI, 1000);

      // Claim functionality
      claimBtn.onclick = async function() {
        if(claimBtn.disabled) return false;
        // Simulate FaucetPay API Call
        claimBtn.disabled = true;
        claimBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        claimResult.innerHTML = '';
        await new Promise(r=>setTimeout(r,1200)); // simulate request
        // Simulate successful payout response
        // In production: send a server-side request to FaucetPay API with user's email and amount
        lastClaim = Math.floor(Date.now() / 1000);
        setLastClaimInfo(email, lastClaim);
        updateUI();
        claimBtn.innerHTML = '<i class="fas fa-hand-holding-usd mr-1"></i> Claim Now';
        claimResult.innerHTML = `
          <span class="inline-flex items-center gap-1 text-green-400 font-semibold">
            <i class="fas fa-check-circle"></i> Sent <span class="font-bold">${claimAmountTRX} TRX</span> to your FaucetPay!
          </span>
        `;
      }
    }

    // --- Load state on page load ---
    window.onload = function() {
      clearAuthError();
      // If user logged in, show faucet, else show login
      const email = localStorage.getItem('trx_faucet_user_email');
      if(email && /^[\w\-\.]+@[\w\-]+\.[\w\-]+$/.test(email)) {
        $('user-email').textContent = email;
        $('auth-section').classList.add('hidden');
        $('faucet-section').classList.remove('hidden');
        loadClaimState();
      } else {
        $('auth-section').classList.remove('hidden');
        $('faucet-section').classList.add('hidden');
        $('email').value = '';
      }
    };
  </script>
</body>
</html>
