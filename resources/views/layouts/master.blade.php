{{-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Infants Vaccination System')</title>
    <!-- Include Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Additional CSS -->
    <link rel="stylesheet" href="{{ asset('css/overview.css') }}">
    <link rel="stylesheet" href="{{ asset('css/tailwind-utilities.css') }}">
    @yield('styles')
    <style>
        /* Base Mobile-First Responsive Styles */
        .sidebar {
            height: 100vh;
            width: 100%;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #7a5bbd;
            padding-top: 50px;
            display: flex;
            flex-direction: column;
            align-items: center;
            color: white;
            z-index: 30;
            overflow-y: auto;
            transform: translateX(-100%);
            transition: transform 0.3s ease;
        }
        
        .sidebar.show {
            transform: translateX(0);
        }
        
        .sidebar h3 {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            margin: 0;
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            color: white;
        }
        
        .sidebar hr {
            border-color: #ffffff50;
            width: 80%;
        }
        
        .menu {
            list-style-type: none;
            width: 80%;
            padding-left: 0;
        }
        
        .menu li {
            margin: 20px 0;
            text-align: left;
        }
        
        .menu li a {
            display: flex;
            align-items: center;
            background-color: white;
            color: black;
            font-weight: bold;
            text-decoration: none;
            font-size: 18px;
            padding: 15px 20px;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }
        
        .menu li a:hover {
            background-color: #e8eaf6;
        }
        
        .menu li a img {
            margin-right: 15px;
            width: 25px;
            height: 25px;
        }
        
        /* Header Styling */
        .main-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 70px;
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 15px;
            z-index: 20;
        }
        
        .hamburger-menu {
            display: block;
            cursor: pointer;
        }
        
        .hamburger-menu span {
            display: block;
            width: 25px;
            height: 3px;
            background-color: #333;
            margin: 5px 0;
            border-radius: 3px;
            transition: transform 0.3s ease;
        }
        
        .header-right {
            display: flex;
            align-items: center;
        }
        
        .main-content {
            margin-left: 0;
            padding: 90px 15px 25px;
            background-color: #F2F2F2;
            min-height: 100vh;
        }
        
        /* Profile Menu Styling */
        .profile-menu {
            position: relative;
            display: inline-block;
        }
        
        .profile-button {
            display: flex;
            align-items: center;
            cursor: pointer;
        }
        
        .profile-button img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
        }
        
        .profile-button span {
            display: none;
        }
        
        .profile-dropdown {
            position: absolute;
            top: 55px;
            right: 0;
            background-color: white;
            min-width: 200px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            border-radius: 8px;
            padding: 10px 0;
            display: none;
            z-index: 40;
        }
        
        .profile-dropdown.show {
            display: block;
        }
        
        .profile-header {
            padding: 10px 15px;
            border-bottom: 1px solid #f1f1f1;
        }
        
        .profile-header h6 {
            margin: 0;
            font-weight: bold;
            color: #333;
        }
        
        .profile-header p {
            margin: 5px 0 0;
            font-size: 13px;
            color: #666;
        }
        
        .dropdown-item {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            text-decoration: none;
            color: #333;
            transition: background-color 0.2s;
        }
        
        .dropdown-item:hover {
            background-color: #f8f9fa;
            text-decoration: none;
        }
        
        .dropdown-item img {
            width: 20px;
            height: 20px;
            margin-right: 10px;
        }
        
        /* Notifications */
        .notifications {
            margin-right: 20px;
            position: relative;
            cursor: pointer;
        }
        
        .notifications img {
            width: 25px;
            height: 25px;
        }
        
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: #ff4757;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: bold;
        }
        
        /* Modal Styling */
        .modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            display: none;
            z-index: 999;
        }
        
        .modal-container {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            border-radius: 8px;
            padding: 25px;
            width: 90%;
            max-width: 500px;
            z-index: 1000;
            display: none;
        }
        
        .modal-container h2 {
            margin-top: 0;
            color: #7a5bbd;
            font-size: 24px;
        }
        
        .modal-container form {
            margin-top: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 25px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
        }
        
        .btn-primary {
            background-color: #7a5bbd;
            color: white;
        }
        
        .btn-secondary {
            background-color: #e9ecef;
            color: #333;
        }
        
        .logout-modal {
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
            display: none;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: #fefefe;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            width: 300px;
        }

        .modal-content p {
            margin: 20px 0;
            font-size: 18px;
        }

        .modal-content button {
            padding: 8px 20px;
            margin: 0 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        #confirmLogoutBtn {
            background-color: #ff4757;
            color: white;
        }

        #cancelLogoutBtn {
            background-color: #e9ecef;
            color: #333;
        }
        
        /* Tablet and Desktop Styles */
        @media (min-width: 768px) {
            .sidebar {
                width: 250px;
                transform: translateX(0);
            }
            
            .hamburger-menu {
                display: none;
            }
            
            .main-header {
                left: 250px;
            }
            
            .main-content {
                margin-left: 250px;
                padding: 90px 25px 25px;
            }
            
            .profile-button span {
                display: block;
            }
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-100">
    <!-- Mobile Menu Toggle -->
    <div class="sidebar" id="sidebar">
        <h3>Health Worker</h3>
        <hr/>
        <ul class="menu">
            <li><a href="{{ route('health_worker.dashboard') }}" class="flex items-center sidebar-link"><img src="{{ asset('images/home (1).png') }}" alt="Dashboard" class="mr-4 w-6 h-6">Dashboard</a></li>
            <li><a href="{{ route('health_worker.patients') }}" class="flex items-center sidebar-link"><img src="{{ asset('images/patient.png') }}" alt="Patient List" class="mr-4 w-6 h-6">Patient List</a></li>
            <li><a href="{{ route('health_worker.vaccination_status') }}" class="flex items-center sidebar-link"><img src="{{ asset('images/vaccination.png') }}" alt="Vaccination Status" class="mr-4 w-6 h-6">Vaccination Status</a></li>
            <li><a href="{{ route('inventory.index') }}" class="flex items-center sidebar-link"><img src="{{ asset('images/checklists.png') }}" alt="Inventory" class="mr-4 w-6 h-6">Inventory</a></li>
            <li><a href="{{ route('health_worker.report') }}" class="flex items-center sidebar-link"><img src="{{ asset('images/bar-chart.png') }}" alt="Report" class="mr-4 w-6 h-6">Report</a></li>
            <li><a href="{{ route('health_worker.feedback') }}" class="flex items-center sidebar-link"><img src="{{ asset('images/feedback.png') }}" alt="Feedback" class="mr-4 w-6 h-6">Feedback</a></li>
        </ul>
    </div>
    
    <!-- Main Header with Profile -->
    <header class="main-header">
        <div class="hamburger-menu" id="menuToggle">
            <span></span>
            <span></span>
            <span></span>
        </div>
        
        <div class="header-right">
            <div class="notifications">
                <img src="{{ asset('images/bell.png') }}" alt="Notifications">
                <span class="notification-badge">0</span>
            </div>
            
            <div class="profile-menu">
                <div class="profile-button" id="profileButton">
                    <img src="{{ asset('images/user (1).png') }}" alt="Profile">
                    <span>{{ Auth::user()->name ?? 'User' }}</span>
                </div>
                <div class="profile-dropdown" id="profileDropdown">
                    <div class="profile-header">
                        <h6>{{ Auth::user()->name ?? 'User' }}</h6>
                        <p>{{ Auth::user()->email ?? 'email@example.com' }}</p>
                    </div>
                    <a href="#" class="dropdown-item" id="changePasswordBtn">
                        <img src="{{ asset('images/padlock.png') }}" alt="Change Password">
                        Change Password
                    </a>
                    <a href="#" class="dropdown-item" id="logoutBtn">
                        <img src="{{ asset('images/logout.png') }}" alt="Logout">
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Main Content -->
    <div class="main-content">
        @yield('content')
    </div>
    
    <!-- Change Password Modal -->
    <div class="modal-backdrop" id="passwordModalBackdrop"></div>
    <div class="modal-container" id="changePasswordModal">
        <h2>Change Password</h2>
        <form id="changePasswordForm" action="{{ route('password.update') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password" required>
            </div>
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>
            <div class="form-group">
                <label for="new_password_confirmation">Confirm New Password</label>
                <input type="password" id="new_password_confirmation" name="new_password_confirmation" required>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" id="cancelPasswordBtn">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Password</button>
            </div>
        </form>
    </div>
    
    <!-- Logout Confirmation Modal -->
    <div id="logoutModal" class="logout-modal">
        <div class="modal-content">
            <p>Are you sure you want to log out?</p>
            <button id="confirmLogoutBtn">Yes</button>
            <button id="cancelLogoutBtn">No</button>
        </div>
    </div>
    
    <script>
        $(document).ready(function() {
            // Mobile menu toggle
            $('#menuToggle').click(function() {
                $('#sidebar').toggleClass('show');
            });
            
            // Toggle profile dropdown
            $('#profileButton').click(function(e) {
                e.stopPropagation();
                $('#profileDropdown').toggleClass('show');
            });
            
            // Close dropdown when clicking elsewhere
            $(document).click(function(e) {
                if (!$(e.target).closest('#sidebar').length && !$(e.target).closest('#menuToggle').length) {
                    $('#sidebar').removeClass('show');
                }
                $('#profileDropdown').removeClass('show');
            });
            
            // Change password modal
            $('#changePasswordBtn').click(function(e) {
                e.preventDefault();
                $('#passwordModalBackdrop').show();
                $('#changePasswordModal').show();
            });
            
            $('#cancelPasswordBtn').click(function() {
                $('#passwordModalBackdrop').hide();
                $('#changePasswordModal').hide();
            });
            
            // Logout functionality
            $('#logoutBtn').click(function(e) {
                e.preventDefault();
                $('#logoutModal').css('display', 'flex');
            });
            
            $('#cancelLogoutBtn').click(function() {
                $('#logoutModal').hide();
            });
            
            $('#confirmLogoutBtn').click(function() {
                // Redirect to logout route
                window.location.href = "{{ route('logout') }}";
            });
            
            // Password validation
            $('#changePasswordForm').submit(function(e) {
                const newPassword = $('#new_password').val();
                const confirmPassword = $('#new_password_confirmation').val();
                
                if (newPassword !== confirmPassword) {
                    e.preventDefault();
                    alert('New password and confirmation password do not match.');
                }
            });
        });
        
        // AJAX navigation for sidebar links
        function ajaxifySidebarLinks() {
            $('.sidebar-link').off('click').on('click', function(e) {
                e.preventDefault();
                var url = $(this).attr('href');
                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(res => res.text())
                    .then(html => {
                        // Extract the .main-content from the response
                        var parser = new DOMParser();
                        var doc = parser.parseFromString(html, 'text/html');
                        var newContent = doc.querySelector('.main-content');
                        if (newContent) {
                            $('.main-content').html(newContent.innerHTML);
                            window.history.pushState({}, '', url);
                            ajaxifySidebarLinks(); // Re-attach for new links
                        }
                    });
            });
        }
        ajaxifySidebarLinks();
        // Handle browser back/forward
        window.addEventListener('popstate', function() {
            fetch(location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(res => res.text())
                .then(html => {
                    var parser = new DOMParser();
                    var doc = parser.parseFromString(html, 'text/html');
                    var newContent = doc.querySelector('.main-content');
                    if (newContent) {
                        $('.main-content').html(newContent.innerHTML);
                        ajaxifySidebarLinks();
                    }
                });
        });
    </script>
    
    @yield('scripts')
</body>
</html> --}}