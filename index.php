<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>LGU3 Local Economic Enterprise — Login</title>

  <!-- Tailwind CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/css/index.css">
</head>
<body class="antialiased font-sans">

  <!-- Background layers -->
  <div class="bg-layer"></div>
  <div class="light-band"></div>
  <div class="light-band band2"></div>
  <div class="particles"></div>

  <!-- glowing spot behind watermark -->
  <div class="logo-spot" aria-hidden="true"></div>

  <!-- logo watermark (actual LGU logo file) -->
  <img src="assets/img/logo.png" alt="" class="watermark" aria-hidden="true" />

  <!-- MAIN LAYOUT -->
  <div class="relative min-h-screen flex items-center justify-center">
    <div class="max-w-6xl w-full mx-auto px-6 lg:px-12">
      <div class="grid grid-cols-1 lg:grid-cols-12 items-center main-grid">


        <!-- LEFT: HERO / DESCRIPTION (lg: col-span-7) -->
        <div class="lg:col-span-7 text-white relative z-10">
          <div class="max-w-xl space-y-6">
            <div class="flex items-start gap-4">
              <div class="flex items-center justify-center">
              </div>

              <div>
                <h1 class="text-4xl lg:text-5xl font-extrabold tracking-tight leading-tight">LGU3 Local Economic Enterprise</h1>
                <p class="mt-2 text-lg text-blue-100/95">Innovation • Transparency • Local Growth</p>
              </div>
            </div>

            <div class="mt-4 text-lg text-blue-50/90 leading-relaxed">
              <p class="float-y">
                Welcome to the LGU3 Enterprise Portal — your secure digital gateway for managing permits,
                enterprise registration, reports, and community services. This system ensures faster processing,
                clear auditing, and reliable access for stakeholders.
              </p>
            </div>

            <!-- Quick feature badges -->
            <div class="mt-6 flex flex-wrap gap-3">
              <span class="inline-flex items-center gap-2 bg-white/8 text-white text-sm px-3 py-2 rounded-full glass">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M5 13l4 4L19 7" />
                </svg>
                Secure Single Sign-on
              </span>
              <span class="inline-flex items-center gap-2 bg-white/8 text-white text-sm px-3 py-2 rounded-full glass">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M3 7h18M3 12h18M3 17h18" />
                </svg>
                Real-time Reports
              </span>
              <span class="inline-flex items-center gap-2 bg-white/8 text-white text-sm px-3 py-2 rounded-full glass">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M4 6h16M4 10h16M4 14h16" />
                </svg>
                Citizen-friendly
              </span>
            </div>

            <!-- small CTA -->
            <div class="mt-8">
              <a href="#" class="inline-flex items-center gap-3 px-5 py-3 rounded-xl font-medium btn-prim text-white shadow-lg hover:shadow-xl transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11.5V11a1 1 0 01-2 0V6.5H7a1 1 0 010-2h4a1 1 0 010 2h-2z" clip-rule="evenodd"/></svg>
                About LGU Services
              </a>
            </div>

            <div class="mt-10 text-sm text-blue-100/70">
              <strong>Contact:</strong> info@lgu3.gov.ph • (02) 1234-5678
            </div>
          </div>
        </div>

        <!-- RIGHT: LOGIN (lg:col-span-5) -->
        <div class="lg:col-span-5 relative z-20 flex items-center justify-center">
          <div class="w-full max-w-md glass rounded-2xl p-1">
            <div class="card-inner">

              <!-- top: logo and heading -->
              <div class="flex flex-col items-center">
                <img src="assets/img/logo.png" alt="LGU Logo" class="w-28 h-28 object-contain mb-3 drop-shadow-lg" />
                <h2 class="text-2xl font-bold text-slate-900">Welcome back</h2>
                <p class="text-sm text-muted mt-1">Sign in to manage enterprise operations</p>
              </div>

              <!-- form -->
              <form class="mt-6 space-y-4" action="actions/login.php" method="POST">
                <!-- email -->
                <label class="block">
                  <div class="relative">
                    <span class="absolute left-3 top-3 text-slate-400">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 12H8m8 0l-4 4m4-4l-4-4M4 6h16v12H4z"/>
                      </svg>
                    </span>
                    <input aria-label="Email" name="email" type="email" required placeholder="Email address"
                           class="w-full pl-12 pr-4 py-3 rounded-xl border border-slate-200 input-focus" />
                  </div>
                </label>

                <!-- password -->
                <label class="block">
                  <div class="relative">
                    <span class="absolute left-3 top-3 text-slate-400">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 11c1.104 0 2 .896 2 2v3H10v-3c0-1.104.896-2 2-2zM8 11V7a4 4 0 118 0v4" />
                      </svg>
                    </span>
                    <input aria-label="Password" name="password" type="password" required placeholder="Password"
                           class="w-full pl-12 pr-4 py-3 rounded-xl border border-slate-200 input-focus" />
                    <button type="button" class="absolute right-3 top-3 text-slate-400" onclick="togglePass(this)" aria-label="Toggle password visibility">
                      <svg xmlns="http://www.w3.org/2000/svg" id="eyeIcon" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                      </svg>
                    </button>
                  </div>
                </label>

                <!-- options -->
                <div class="flex items-center justify-between text-sm">
                  <label class="inline-flex items-center gap-2">
                    <input type="checkbox" class="rounded border-slate-300" /> <span class="text-sm text-slate-700">Remember me</span>
                  </label>
                  <a href="#" class="text-sm text-blue-600 hover:underline">Forgot password?</a>
                </div>

                <!-- submit -->
                <div>
                  <button type="submit" class="w-full rounded-xl py-3 text-white font-semibold btn-prim transition transform">
                    Sign in
                  </button>
                </div>
              </form>

              <!-- secondary links -->
              <div class="mt-5 text-center">
                <p class="text-sm text-slate-600">Don't have an account?
                  <a href="register.php" class="text-blue-600 font-medium hover:underline">Sign up</a>
                </p>
                <p class="text-xs text-slate-500 mt-3">By signing in you agree to our <a href="#" class="text-blue-600 hover:underline">Terms & Agreement</a>.</p>
              </div>

            </div> <!-- card-inner -->
          </div> <!-- glass -->
        </div> <!-- right -->
      </div>
    </div>

    <!-- footer bar -->
    <footer class="absolute bottom-6 left-0 right-0 text-center z-30">
      <div class="text-xs text-white/80">© <span id="year"></span> LGU3 Local Economic Enterprise • All Rights Reserved</div>
    </footer>
  </div>

  <!-- small JS for interactions (toggle pass + fake sign-in micro feedback + year) -->
  <script>
    // set current year
    document.getElementById('year').textContent = new Date().getFullYear();

    // toggle password visibility
    function togglePass(btn){
      const form = btn.closest('form');
      if(!form) return;
      const pw = form.querySelector('input[type="password"], input[type="text"][name="password"]');
      if(!pw) return;
      if(pw.type === 'password'){ pw.type = 'text'; btn.title = 'Hide password'; }
      else { pw.type = 'password'; btn.title = 'Show password'; }
      // swap icon (simple)
      btn.querySelector('svg').classList.toggle('text-blue-600');
    }

  </script>
</body>
</html>
