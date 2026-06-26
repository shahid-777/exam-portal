<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Exam Portal - Secure Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white flex items-center justify-center min-h-screen font-sans px-4">

    <div class="bg-gray-800 p-8 rounded-2xl shadow-2xl w-full max-w-md border border-gray-700">
        <h1 class="text-3xl font-black text-blue-500 mb-2 tracking-wide text-center">ONLINE EXAM PORTAL</h1>
        <p class="text-gray-400 mb-6 text-sm text-center">Sign in securely using your academic credentials.</p>
        
        <form id="login-form" class="space-y-5">
            <div>
                <label class="block text-xs font-bold uppercase text-gray-400 mb-1 tracking-wider">Email Address</label>
                <input type="email" id="login-email" required placeholder="example@email.com" class="w-full bg-gray-700 border border-gray-600 rounded-xl p-3 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-blue-500 font-semibold">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-gray-400 mb-1 tracking-wider">Password</label>
                <input type="password" id="login-password" required placeholder="••••••••" class="w-full bg-gray-700 border border-gray-600 rounded-xl p-3 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-blue-500 font-semibold">
            </div>
            
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-black py-3.5 px-4 rounded-xl transition duration-300 shadow-md transform active:scale-95 uppercase text-sm tracking-wider">
                Sign In To Account
            </button>
        </form>

        <div class="mt-6 text-center text-xs text-gray-500 font-medium">
            Direct database sync via local host engine.
        </div>
    </div>

    <script>
        document.getElementById('login-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('login-email').value.trim();
            const password = document.getElementById('login-password').value;

            // Transmit data securely to backend bridge
            fetch('api.php?action=custom_login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: email, password: password })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    // Global LocalStorage state variables set
                    localStorage.setItem('userEmail', data.user.email);
                    localStorage.setItem('userName', data.user.name);
                    
                    // Structural access routing based on assigned role
                    if (data.user.role === 'admin') {
                        window.location.href = "admin.html";
                    } else {
                        window.location.href = "dashboard.html";
                    }
                } else {
                    alert("Authentication Failure: " + data.message);
                }
            })
            .catch(() => {
                alert("Critical API Mismatch: Ensure 'api.php' is active and configured correctly in the same folder.");
            });
        });
    </script>
</body>
</html>