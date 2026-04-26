<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Universal RFID Access Control</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- DataTables Bootstrap 5 CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --accent: #4895ef;
            --success: #4cc9f0;
            --danger: #f72585;
            --dark: #212529;
            --light: #f8f9fa;
            --slate: #2c3e50;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f0f2f5;
            color: #333;
            overflow-x: hidden;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 280px;
            background-color: var(--slate);
            color: #fff;
            transition: all 0.3s;
            z-index: 1000;
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar-header {
            padding: 30px 20px;
            text-align: center;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
        }
        
        .sidebar-header h4 {
            margin: 0;
            font-weight: 700;
            font-size: 1.2rem;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .sidebar-menu {
            padding: 20px 10px;
            list-style: none;
            margin: 0;
        }

        .sidebar-menu li {
            margin-bottom: 5px;
        }

        .sidebar-menu li a {
            color: #cbd5e0;
            text-decoration: none;
            display: flex;
            align-items: center;
            font-size: 0.95rem;
            transition: 0.3s;
            border-radius: 10px;
            padding: 12px 20px;
        }

        .sidebar-menu li a i {
            margin-right: 15px;
            font-size: 1.2rem;
        }

        .sidebar-menu li a:hover, .sidebar-menu li a.active {
            background-color: rgba(255,255,255,0.1);
            color: #fff;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .sidebar-menu li a.active {
            background-color: var(--primary);
        }

        /* Main Content */
        .main-content {
            margin-left: 280px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .topbar {
            height: 70px;
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 40px;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .breadcrumb-area h5 {
            margin: 0;
            font-weight: 600;
            color: var(--slate);
        }

        .profile-section {
            display: flex;
            align-items: center;
        }

        .profile-section .username {
            font-weight: 600;
            font-size: 0.9rem;
            color: #555;
            margin-right: 15px;
        }

        .profile-section img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid var(--primary);
            padding: 2px;
        }

        .content-area {
            padding: 30px;
            flex-grow: 1;
        }

        /* Modern & Clean Cards */
        .card {
            border: none;
            border-radius: 15px;
            background-color: #fff;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 25px;
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card {
            padding: 25px;
            display: flex;
            align-items: center;
        }

        .stat-icon-wrapper {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-right: 20px;
            color: #fff;
        }

        .bg-gradient-primary { background: linear-gradient(135deg, #4361ee, #4cc9f0); }
        .bg-gradient-success { background: linear-gradient(135deg, #2ecc71, #27ae60); }
        .bg-gradient-info { background: linear-gradient(135deg, #1abc9c, #16a085); }
        .bg-gradient-danger { background: linear-gradient(135deg, #e74c3c, #c0392b); }

        .stat-details h6 {
            font-size: 0.8rem;
            color: #7f8c8d;
            text-transform: uppercase;
            font-weight: 700;
            margin: 0 0 5px 0;
        }

        .stat-details h3 {
            font-size: 1.8rem;
            font-weight: 700;
            margin: 0;
            color: var(--slate);
        }

        .card-header {
            background-color: transparent;
            border-bottom: 1px solid #f1f1f1;
            padding: 20px 25px;
        }

        .card-header h5 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--slate);
        }

        .view-section { display: none; }
        .view-section.active { display: block; animation: slideIn 0.4s ease; }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Wizard Styles */
        .wizard-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            position: relative;
        }

        .wizard-steps::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            height: 2px;
            background: #eee;
            z-index: 1;
        }

        .step-item {
            position: relative;
            z-index: 2;
            text-align: center;
            flex: 1;
        }

        .step-circle {
            width: 40px;
            height: 40px;
            background: #fff;
            border: 2px solid #eee;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-weight: 700;
            color: #999;
            transition: 0.3s;
        }

        .step-item.active .step-circle {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
            box-shadow: 0 0 15px rgba(67, 97, 238, 0.4);
        }

        .step-item.completed .step-circle {
            background: var(--success);
            border-color: var(--success);
            color: #fff;
        }

        .step-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: #999;
        }

        .step-item.active .step-label {
            color: var(--primary);
        }

        .wizard-content {
            padding: 20px;
        }

        .wizard-pane {
            display: none;
        }

        .wizard-pane.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* RFID Scan Animation */
        .scan-animation {
            text-align: center;
            padding: 40px;
        }

        .rfid-svg {
            width: 150px;
            height: 150px;
            margin-bottom: 20px;
        }

        .pulse {
            animation: pulse-animation 2s infinite;
        }

        @keyframes pulse-animation {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.7; }
            100% { transform: scale(1); opacity: 1; }
        }

        /* Badges */
        .badge-active { background-color: #e8f8f1; color: #2ecc71; padding: 6px 12px; border-radius: 20px; font-weight: 600; font-size: 0.75rem;}
        .badge-frozen { background-color: #fdf2f2; color: #e74c3c; padding: 6px 12px; border-radius: 20px; font-weight: 600; font-size: 0.75rem;}
        .badge-online { background-color: #e8f8f1; color: #2ecc71; }
        .badge-offline { background-color: #fdf2f2; color: #e74c3c; }

        .btn-rounded { border-radius: 10px; }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h4><i class="bi bi-cpu-fill me-2"></i> Universal RFID</h4>
        </div>
        <ul class="sidebar-menu">
            <li><a href="#" onclick="switchView('dashboard')" id="nav-dashboard" class="active"><i class="bi bi-grid-fill"></i> Dashboard</a></li>
            <li><a href="#" onclick="switchView('users')" id="nav-users"><i class="bi bi-people-fill"></i> Member Directory</a></li>
            <li><a href="#" onclick="switchView('orgs')" id="nav-orgs"><i class="bi bi-building-fill"></i> Organizations</a></li>
            <li><a href="#" onclick="switchView('devices')" id="nav-devices"><i class="bi bi-broadcast-pin"></i> Devices</a></li>
            <li><a href="#" onclick="switchView('logs')" id="nav-logs"><i class="bi bi-file-earmark-medical-fill"></i> Activity Logs</a></li>
            <li><a href="#" onclick="switchView('settings')" id="nav-settings"><i class="bi bi-gear-wide-connected"></i> System Rules</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar -->
        <div class="topbar">
            <div class="breadcrumb-area">
                <h5 id="current-view-title">Dashboard Overview</h5>
            </div>
            <div class="profile-section">
                <span class="username">Master Admin</span>
                <img src="https://ui-avatars.com/api/?name=Admin&background=4361ee&color=fff" alt="Admin">
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            
            <!-- Dashboard -->
            <div id="view-dashboard" class="view-section active">
                <div class="row">
                    <div class="col-lg-3 col-md-6">
                        <div class="card stat-card">
                            <div class="stat-icon-wrapper bg-gradient-primary"><i class="bi bi-people"></i></div>
                            <div class="stat-details">
                                <h6>Total Members</h6>
                                <h3 id="stat-total-users">0</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="card stat-card">
                            <div class="stat-icon-wrapper bg-gradient-success"><i class="bi bi-shield-check"></i></div>
                            <div class="stat-details">
                                <h6>Entries Today</h6>
                                <h3 id="stat-entries-today">0</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="card stat-card">
                            <div class="stat-icon-wrapper bg-gradient-info"><i class="bi bi-check2-circle"></i></div>
                            <div class="stat-details">
                                <h6>Active Status</h6>
                                <h3 id="stat-active-users">0</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="card stat-card">
                            <div class="stat-icon-wrapper bg-gradient-danger"><i class="bi bi-shield-slash"></i></div>
                            <div class="stat-details">
                                <h6>Failed Today</h6>
                                <h3 id="stat-failed-today">0</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header"><h5>Weekly Access Traffic</h5></div>
                            <div class="card-body"><canvas id="accessChart" height="300"></canvas></div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header"><h5>Member Status Distribution</h5></div>
                            <div class="card-body"><canvas id="statusChart" height="300"></canvas></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Users -->
            <div id="view-users" class="view-section">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4>Member Management</h4>
                    <button class="btn btn-primary btn-rounded shadow-sm px-4" onclick="openRegistrationWizard()">
                        <i class="bi bi-person-plus-fill me-2"></i> Onboard New Member
                    </button>
                </div>
                <div class="card">
                    <div class="card-body">
                        <table id="usersTable" class="table w-100">
                            <thead>
                                <tr>
                                    <th>Member</th>
                                    <th>Org / Dept</th>
                                    <th>Role / ID</th>
                                    <th>Card UID</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="usersTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Organizations -->
            <div id="view-orgs" class="view-section">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4>Organization Hierarchy</h4>
                    <button class="btn btn-dark btn-rounded px-4" data-bs-toggle="modal" data-bs-target="#addOrgModal">
                        <i class="bi bi-plus-lg me-2"></i> New Organization
                    </button>
                </div>
                
                <div class="row" id="orgs-container">
                    <!-- Orgs will be loaded here -->
                </div>
            </div>

            <!-- Devices -->
            <div id="view-devices" class="view-section">
                <h4 class="mb-4">Hardware Management</h4>
                <div class="row" id="devices-container">
                    <!-- Devices will be loaded here -->
                </div>
            </div>

            <!-- Logs -->
            <div id="view-logs" class="view-section">
                <h4 class="mb-4">Real-time Activity Stream</h4>
                <div class="card">
                    <div class="card-body">
                        <table id="logsTable" class="table w-100">
                            <thead>
                                <tr>
                                    <th>Timestamp</th>
                                    <th>Member</th>
                                    <th>Device</th>
                                    <th>Event</th>
                                    <th>Card UID</th>
                                </tr>
                            </thead>
                            <tbody id="logsTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Settings -->
            <div id="view-settings" class="view-section">
                <h4 class="mb-4">System Configuration</h4>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header"><h5>Operation Mode</h5></div>
                            <div class="card-body">
                                <p class="text-muted small">Authentication mode validates cards. Registration mode captures new cards for onboarding.</p>
                                <div class="btn-group w-100 mt-2">
                                    <input type="radio" class="btn-check" name="sys_mode" id="mode_auth" onclick="setMode('auth_mod')">
                                    <label class="btn btn-outline-primary" for="mode_auth">Authentication Mode</label>
                                    
                                    <input type="radio" class="btn-check" name="sys_mode" id="mode_reg" onclick="setMode('reg_mod')">
                                    <label class="btn btn-outline-warning" for="mode_reg">Registration Mode</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header"><h5>Security Constraints</h5></div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Daily Access Quota (0 = Unlimited)</label>
                                    <input type="number" id="set_max_access" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Auto-Freeze Fail Threshold</label>
                                    <input type="number" id="set_max_failed" class="form-control">
                                </div>
                                <button class="btn btn-primary w-100 btn-rounded" onclick="saveAdvancedSettings()">Apply Rules</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Registration Wizard Modal -->
    <div class="modal fade" id="wizardModal" data-bs-backdrop="static" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
          <div class="modal-header bg-light border-0">
            <h5 class="modal-title fw-bold">Onboarding Wizard</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="stopScanPolling()"></button>
          </div>
          <div class="modal-body p-4">
            <!-- Progress Bar -->
            <div class="wizard-steps">
                <div class="step-item active" id="step-1-indicator">
                    <div class="step-circle">1</div>
                    <div class="step-label">Basic Info</div>
                </div>
                <div class="step-item" id="step-2-indicator">
                    <div class="step-circle">2</div>
                    <div class="step-label">Organization</div>
                </div>
                <div class="step-item" id="step-3-indicator">
                    <div class="step-circle">3</div>
                    <div class="step-label">RFID Scan</div>
                </div>
                <div class="step-item" id="step-4-indicator">
                    <div class="step-circle">4</div>
                    <div class="step-label">Confirm</div>
                </div>
            </div>

            <form id="wizardForm">
                <!-- Step 1: Basic Info -->
                <div class="wizard-pane active" id="pane-1">
                    <h6 class="fw-bold mb-3">Step 1: Personal Details</h6>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label small fw-bold">Full Name *</label>
                            <input type="text" id="wiz_name" class="form-control" required placeholder="Enter full name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Email Address</label>
                            <input type="email" id="wiz_email" class="form-control" placeholder="name@example.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Phone Number</label>
                            <input type="tel" id="wiz_phone" class="form-control" placeholder="+1234567890">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Gender</label>
                            <select id="wiz_gender" class="form-select">
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                                <option value="prefer_not_to_say">Prefer not to say</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Profile Photo (Optional)</label>
                            <input type="file" id="wiz_photo" class="form-control" accept="image/*">
                        </div>
                    </div>
                </div>

                <!-- Step 2: Organization Details -->
                <div class="wizard-pane" id="pane-2">
                    <h6 class="fw-bold mb-3">Step 2: Organizational Hierarchy</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Organization *</label>
                            <select id="wiz_org" class="form-select" onchange="loadDeptOptions(this.value)" required>
                                <option value="">Select Organization</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Department *</label>
                            <select id="wiz_dept" class="form-select" onchange="loadSectionOptions(this.value)" required disabled>
                                <option value="">Select Department</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Section / Unit</label>
                            <select id="wiz_sect" class="form-select" disabled>
                                <option value="">Select Section</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Member ID / ID Card #</label>
                            <input type="text" id="wiz_member_id" class="form-control" placeholder="ID-001">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small fw-bold">Role / Position</label>
                            <input type="text" id="wiz_role" class="form-control" placeholder="e.g. Manager, Student, Doctor">
                        </div>
                    </div>
                </div>

                <!-- Step 3: RFID Scan -->
                <div class="wizard-pane" id="pane-3">
                    <h6 class="fw-bold mb-3">Step 3: Card Enrollment</h6>
                    <div class="scan-animation">
                        <svg class="rfid-svg pulse" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <rect x="2" y="5" width="20" height="14" rx="2" stroke="var(--primary)"></rect>
                            <path d="M7 10h10M7 14h5" stroke="var(--primary)"></path>
                            <circle cx="17" cy="14" r="2" fill="var(--primary)"></circle>
                        </svg>
                        <h5 class="fw-bold text-primary">Scan Card Now</h5>
                        <p class="text-muted small">Please scan the RFID card on the connected device...</p>
                        
                        <div class="mt-4">
                            <label class="form-label small fw-bold">Capture from Device:</label>
                            <div class="input-group w-75 mx-auto">
                                <select id="wiz_device" class="form-select" onchange="startScanPolling()">
                                    <option value="">Select Capture Device</option>
                                </select>
                                <button class="btn btn-outline-primary" type="button" onclick="loadDevicesForWizard()">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mt-4 w-75 mx-auto">
                            <label class="form-label small fw-bold">Enrolled Card UID:</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-credit-card-2-front"></i></span>
                                <input type="text" id="wiz_card_uid_input" class="form-control font-monospace" placeholder="Wait for scan or type manually..." oninput="capturedUid = this.value">
                            </div>
                        </div>

                        <div id="scan-status" class="mt-3">
                            <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                            <span class="text-primary fw-600" id="scan-status-text">Waiting for device selection...</span>
                        </div>
                        
                        <div id="scan-success" class="mt-3 d-none">
                            <div class="alert alert-success d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-check-circle-fill me-2"></i>
                                    <span>Card Detected: <strong id="detected_uid" class="font-monospace">---</strong></span>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearCapturedScan()">
                                    <i class="bi bi-x-circle"></i> Clear
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Confirm -->
                <div class="wizard-pane" id="pane-4">
                    <h6 class="fw-bold mb-3">Step 4: Review & Finalize</h6>
                    <div class="bg-light p-3 rounded-3 mb-3">
                        <div class="row g-2">
                            <div class="col-6 small text-muted text-uppercase fw-bold">Full Name:</div>
                            <div class="col-6 small fw-bold" id="rev_name">---</div>
                            <div class="col-6 small text-muted text-uppercase fw-bold">Organization:</div>
                            <div class="col-6 small fw-bold" id="rev_org">---</div>
                            <div class="col-6 small text-muted text-uppercase fw-bold">ID / Card UID:</div>
                            <div class="col-6 small fw-bold" id="rev_card">---</div>
                        </div>
                    </div>
                    <div class="alert alert-info py-2 small">
                        <i class="bi bi-info-circle me-2"></i> After saving, this member will have immediate access permissions.
                    </div>
                </div>
            </form>
          </div>
          <div class="modal-footer border-0 bg-light">
            <button type="button" class="btn btn-link text-muted text-decoration-none" id="btn-prev" onclick="moveWizard(-1)">Previous</button>
            <button type="button" class="btn btn-primary px-4 btn-rounded" id="btn-next" onclick="moveWizard(1)">Next Step</button>
            <button type="button" class="btn btn-success px-4 btn-rounded d-none" id="btn-save" onclick="finalizeRegistration()">Confirm & Save</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modals for Management -->
    <!-- Add Org Modal -->
    <div class="modal fade" id="addOrgModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"><h5>Add New Organization</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="text" id="new_org_name" class="form-control" placeholder="Organization Name">
                </div>
                <div class="modal-footer"><button class="btn btn-primary btn-rounded w-100" onclick="addOrg()">Create Organization</button></div>
            </div>
        </div>
    </div>

    <!-- Add Dept Modal -->
    <div class="modal fade" id="addDeptModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"><h5>Add Department</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="hidden" id="dept_org_id">
                    <input type="text" id="new_dept_name" class="form-control" placeholder="Department Name">
                </div>
                <div class="modal-footer"><button class="btn btn-primary btn-rounded w-100" onclick="addDept()">Add Department</button></div>
            </div>
        </div>
    </div>

    <!-- Add Section Modal -->
    <div class="modal fade" id="addSectionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"><h5>Add Section / Unit</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="hidden" id="sect_dept_id">
                    <input type="text" id="new_sect_name" class="form-control" placeholder="Section Name">
                </div>
                <div class="modal-footer"><button class="btn btn-primary btn-rounded w-100" onclick="addSection()">Add Section</button></div>
            </div>
        </div>
    </div>

    <!-- Edit Device Modal -->
    <div class="modal fade" id="editDeviceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"><h5>Configure Device</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="hidden" id="edit_dev_id">
                    <div class="mb-3"><label class="form-label small fw-bold">Device Name</label><input type="text" id="edit_dev_name" class="form-control"></div>
                    <div class="mb-3"><label class="form-label small fw-bold">Assignment</label><select id="edit_dev_org" class="form-select"><option value="">Unassigned</option></select></div>
                    <div class="mb-3"><label class="form-label small fw-bold">Location</label><input type="text" id="edit_dev_loc" class="form-control"></div>
                </div>
                <div class="modal-footer"><button class="btn btn-primary btn-rounded w-100" onclick="saveDevice()">Update Device</button></div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        const apiUrl = 'api/dashboard_api.php';
        let currentStep = 1;
        let scanInterval = null;
        let capturedUid = null;
        let accessChart = null;
        let statusChart = null;

        $(document).ready(function() {
            switchView('dashboard');
            loadOrgsForWizard();
        });

        function switchView(viewId) {
            $('.view-section').removeClass('active');
            $('.sidebar-menu a').removeClass('active');
            $('#view-' + viewId).addClass('active');
            $('#nav-' + viewId).addClass('active');

            const titles = {
                'dashboard': 'Dashboard Overview',
                'users': 'Member Directory',
                'orgs': 'Organization Management',
                'devices': 'Hardware Management',
                'logs': 'Activity Logs',
                'settings': 'System Configuration'
            };
            $('#current-view-title').text(titles[viewId]);

            if(viewId === 'dashboard') { loadStats(); initCharts(); }
            if(viewId === 'users') loadUsers();
            if(viewId === 'orgs') loadOrgs();
            if(viewId === 'devices') loadDevices();
            if(viewId === 'logs') loadLogs();
            if(viewId === 'settings') loadSettings();
        }

        // --- Stats & Charts ---
        function loadStats() {
            fetch(apiUrl + '?action=get_stats').then(r => r.json()).then(d => {
                $('#stat-total-users').text(d.total_users);
                $('#stat-active-users').text(d.active_users);
                $('#stat-entries-today').text(d.entries_today);
                $('#stat-failed-today').text(d.failed_today);
            });
        }

        function initCharts() {
            fetch(apiUrl + '?action=get_chart_data').then(r => r.json()).then(d => {
                const ctx = document.getElementById('accessChart').getContext('2d');
                if(accessChart) accessChart.destroy();
                accessChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: d.labels,
                        datasets: [
                            { label: 'Granted', data: d.granted, borderColor: '#4361ee', backgroundColor: 'rgba(67, 97, 238, 0.1)', fill: true, tension: 0.4 },
                            { label: 'Denied', data: d.denied, borderColor: '#f72585', backgroundColor: 'rgba(247, 37, 133, 0.1)', fill: true, tension: 0.4 }
                        ]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
                });
            });

            fetch(apiUrl + '?action=get_stats').then(r => r.json()).then(d => {
                const ctx = document.getElementById('statusChart').getContext('2d');
                if(statusChart) statusChart.destroy();
                statusChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Active', 'Frozen'],
                        datasets: [{ data: [d.active_users, d.total_users - d.active_users], backgroundColor: ['#4cc9f0', '#f72585'], borderWidth: 0 }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, cutout: '70%', plugins: { legend: { position: 'bottom' } } }
                });
            });
        }

        // --- User Management ---
        function loadUsers() {
            fetch(apiUrl + '?action=get_users').then(r => r.json()).then(users => {
                if ($.fn.DataTable.isDataTable('#usersTable')) $('#usersTable').DataTable().destroy();
                let h = '';
                users.forEach(u => {
                    let b = u.status === 'active' ? '<span class="badge-active">Active</span>' : '<span class="badge-frozen">Frozen</span>';
                    let act = u.status === 'active' ? 
                        `<button class="btn btn-sm btn-light border" onclick="freezeUser(${u.id})"><i class="bi bi-pause text-warning"></i></button>` :
                        `<button class="btn btn-sm btn-light border" onclick="unfreezeUser(${u.id})"><i class="bi bi-play text-success"></i></button>`;
                    
                    let photoUrl = u.photo_path ? u.photo_path : `https://ui-avatars.com/api/?name=${u.full_name}&background=random`;
                    
                    h += `<tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="${photoUrl}" class="rounded-circle me-2" width="30" height="30" style="object-fit: cover;">
                                <div><div class="fw-bold">${u.full_name}</div><small class="text-muted">${u.email || 'No email'}</small></div>
                            </div>
                        </td>
                        <td><small>${u.org_name || 'N/A'}</small><br><small class="text-muted">${u.dept_name || '-'}</small></td>
                        <td><small>${u.role || '-'}</small><br><small class="text-muted">#${u.member_id || '-'}</small></td>
                        <td><code class="text-primary small">${u.card_uid}</code></td>
                        <td>${b}</td>
                        <td>
                            <div class="btn-group">
                                ${act}
                                <button class="btn btn-sm btn-light border" onclick="deleteUser(${u.id})"><i class="bi bi-trash text-danger"></i></button>
                            </div>
                        </td>
                    </tr>`;
                });
                $('#usersTableBody').html(h);
                $('#usersTable').DataTable({ order: [[0, 'asc']] });
            });
        }

        // --- Organization Management ---
        function loadOrgs() {
            fetch(apiUrl + '?action=get_orgs').then(r => r.json()).then(orgs => {
                let h = '';
                orgs.forEach(o => {
                    h += `<div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center bg-light">
                                <h6 class="mb-0 fw-bold"><i class="bi bi-building me-2"></i> ${o.name}</h6>
                                <button class="btn btn-sm btn-primary btn-rounded" onclick="openAddDept(${o.id})"><i class="bi bi-plus"></i> Dept</button>
                            </div>
                            <div class="card-body" id="org-depts-${o.id}">
                                <div class="text-center py-3"><div class="spinner-border spinner-border-sm text-secondary"></div></div>
                            </div>
                        </div>
                    </div>`;
                    loadDepts(o.id);
                });
                $('#orgs-container').html(h || '<div class="col-12 text-center text-muted py-5">No organizations found.</div>');
            });
        }

        function loadDepts(orgId) {
            fetch(apiUrl + `?action=get_depts&org_id=${orgId}`).then(r => r.json()).then(depts => {
                let h = '<ul class="list-group list-group-flush">';
                if(depts.length === 0) h += '<li class="list-group-item text-muted small">No departments</li>';
                depts.forEach(d => {
                    h += `<li class="list-group-item d-flex justify-content-between align-items-center py-2 border-0">
                        <span class="small fw-600"><i class="bi bi-diagram-2 me-2"></i> ${d.name}</span>
                        <button class="btn btn-xs btn-light text-primary border" onclick="openAddSection(${d.id})"><i class="bi bi-plus"></i></button>
                    </li>
                    <div class="ps-4 mb-2" id="dept-sections-${d.id}"></div>`;
                    loadSections(d.id);
                });
                h += '</ul>';
                $(`#org-depts-${orgId}`).html(h);
            });
        }

        function loadSections(deptId) {
            fetch(apiUrl + `?action=get_sections&dept_id=${deptId}`).then(r => r.json()).then(sects => {
                let h = '';
                sects.forEach(s => {
                    h += `<div class="text-muted small py-1 ps-2 border-start border-2 ms-2 mb-1">
                        <i class="bi bi-dash me-1"></i> ${s.name}
                    </div>`;
                });
                $(`#dept-sections-${deptId}`).html(h);
            });
        }

        // --- Device Management ---
        function loadDevices() {
            fetch(apiUrl + '?action=get_devices').then(r => r.json()).then(devices => {
                let h = '';
                devices.forEach(d => {
                    let s = d.status === 'online' ? '<span class="badge badge-online ms-2 small" style="padding: 2px 8px; border-radius: 10px;">ONLINE</span>' : '<span class="badge badge-offline ms-2 small" style="padding: 2px 8px; border-radius: 10px;">OFFLINE</span>';
                    h += `<div class="col-md-4 mb-4">
                        <div class="card stat-card border-top border-4 border-primary">
                            <div class="w-100">
                                <div class="d-flex justify-content-between">
                                    <h6 class="fw-bold mb-1">${d.name} ${s}</h6>
                                    <button class="btn btn-sm btn-link p-0" onclick="openEditDevice(${JSON.stringify(d).replace(/"/g, '&quot;')})"><i class="bi bi-pencil-square"></i></button>
                                </div>
                                <div class="small text-muted mb-3">UID: <span class="font-monospace">${d.device_uid}</span></div>
                                <div class="small mb-1"><i class="bi bi-building me-2"></i> Org: <b>${d.org_name || 'Unassigned'}</b></div>
                                <div class="small mb-2"><i class="bi bi-geo-alt me-2"></i> Loc: <b>${d.location || 'Unknown'}</b></div>
                                <div class="small text-muted border-top pt-2 mt-2">Last seen: ${d.last_seen || 'Never'}</div>
                            </div>
                        </div>
                    </div>`;
                });
                $('#devices-container').html(h || '<div class="col-12 text-center text-muted py-5">No devices detected.</div>');
            });
        }

        // --- Logs ---
        function loadLogs() {
            fetch(apiUrl + '?action=get_logs').then(r => r.json()).then(logs => {
                if ($.fn.DataTable.isDataTable('#logsTable')) $('#logsTable').DataTable().destroy();
                let h = '';
                logs.forEach(l => {
                    let c = l.action.includes('Granted') ? 'text-success fw-bold' : 'text-danger fw-bold';
                    h += `<tr>
                        <td><small>${l.timestamp}</small></td>
                        <td>${l.user_name || '<i class="text-muted">Unknown</i>'}</td>
                        <td><small>${l.device_name || 'N/A'}</small></td>
                        <td class="${c}">${l.action}</td>
                        <td><code class="small">${l.card_uid}</code></td>
                    </tr>`;
                });
                $('#logsTableBody').html(h);
                $('#logsTable').DataTable({ order: [[0, 'desc']] });
            });
        }

        // --- Wizard Logic ---
        function openRegistrationWizard() {
            currentStep = 1;
            capturedUid = null;
            $('#wizardForm')[0].reset();
            updateWizardUI();
            $('#wiz_device').html('<option value="">Detecting Devices...</option>');
            loadDevicesForWizard();
            $('#wizardModal').modal('show');
            setMode('reg_mod'); // Switch system to registration mode automatically
        }

        function loadOrgsForWizard() {
            fetch(apiUrl + '?action=get_orgs').then(r => r.json()).then(orgs => {
                let h = '<option value="">Select Organization</option>';
                orgs.forEach(o => h += `<option value="${o.id}">${o.name}</option>`);
                $('#wiz_org').html(h);
            });
        }

        function loadDeptOptions(orgId) {
            if(!orgId) { $('#wiz_dept').prop('disabled', true).html('<option value="">Select Department</option>'); return; }
            fetch(apiUrl + `?action=get_depts&org_id=${orgId}`).then(r => r.json()).then(depts => {
                let h = '<option value="">Select Department</option>';
                depts.forEach(d => h += `<option value="${d.id}">${d.name}</option>`);
                $('#wiz_dept').prop('disabled', false).html(h);
            });
        }

        function loadSectionOptions(deptId) {
            if(!deptId) { $('#wiz_sect').prop('disabled', true).html('<option value="">Select Section</option>'); return; }
            fetch(apiUrl + `?action=get_sections&dept_id=${deptId}`).then(r => r.json()).then(sects => {
                let h = '<option value="">Select Section</option>';
                sects.forEach(s => h += `<option value="${s.id}">${s.name}</option>`);
                $('#wiz_sect').prop('disabled', false).html(h);
            });
        }

        function loadDevicesForWizard() {
            fetch(apiUrl + '?action=get_devices').then(r => r.json()).then(devices => {
                let h = '<option value="">Select Capture Device</option>';
                devices.forEach(d => h += `<option value="${d.device_uid}">${d.name} (${d.location})</option>`);
                $('#wiz_device').html(h);
                $('#edit_dev_org').html('<option value="">Unassigned</option>');
                fetch(apiUrl + '?action=get_orgs').then(r => r.json()).then(orgs => {
                    orgs.forEach(o => $('#edit_dev_org').append(`<option value="${o.id}">${o.name}</option>`));
                });
            });
        }

        function moveWizard(dir) {
            if(dir === 1) {
                if(currentStep === 1 && !$('#wiz_name').val()) { alert('Name is required'); return; }
                if(currentStep === 2 && !$('#wiz_org').val()) { alert('Organization is required'); return; }
                if(currentStep === 3 && !capturedUid) { alert('Please scan a card first'); return; }
            }

            currentStep += dir;
            if(currentStep === 3) {
                $('#wiz_card_uid_input').val(capturedUid || '');
                startScanPolling();
            }
            else stopScanPolling();

            if(currentStep === 4) {
                $('#rev_name').text($('#wiz_name').val());
                $('#rev_org').text($('#wiz_org option:selected').text());
                $('#rev_card').text(capturedUid || 'NOT SCANNED');
            }

            updateWizardUI();
        }

        function updateWizardUI() {
            $('.wizard-pane').removeClass('active');
            $(`#pane-${currentStep}`).addClass('active');
            $('.step-item').removeClass('active completed');
            for(let i=1; i<currentStep; i++) $(`#step-${i}-indicator`).addClass('completed');
            $(`#step-${currentStep}-indicator`).addClass('active');

            $('#btn-prev').toggleClass('d-none', currentStep === 1);
            $('#btn-next').toggleClass('d-none', currentStep === 4);
            $('#btn-save').toggleClass('d-none', currentStep !== 4);
        }

        function startScanPolling() {
            const devUid = $('#wiz_device').val();
            if(!devUid) { 
                $('#scan-status').html('<span class="text-danger">Please select a device to start listening...</span>');
                return;
            }
            $('#scan-status').html('<div class="spinner-border spinner-border-sm text-primary me-2"></div><span class="text-primary fw-600">Waiting for live signal from '+devUid+'...</span>');
            
            if(scanInterval) clearInterval(scanInterval);
            scanInterval = setInterval(() => {
                fetch(`api/check_scan.php?deviceID=${devUid}`).then(r => r.json()).then(d => {
                    if(d.success) {
                        capturedUid = d.uid;
                        $('#wiz_card_uid_input').val(capturedUid);
                        $('#detected_uid').text(capturedUid);
                        $('#scan-status').addClass('d-none');
                        $('#scan-success').removeClass('d-none');
                        stopScanPolling();
                    }
                });
            }, 2000);
        }

        function stopScanPolling() {
            if(scanInterval) clearInterval(scanInterval);
            scanInterval = null;
        }

        function clearCapturedScan() {
            capturedUid = null;
            $('#wiz_card_uid_input').val('');
            $('#detected_uid').text('---');
            $('#scan-success').addClass('d-none');
            $('#scan-status').removeClass('d-none');
            startScanPolling();
        }

        function finalizeRegistration() {
            let formData = new FormData();
            formData.append('action', 'save_user_wizard');
            formData.append('full_name', $('#wiz_name').val());
            formData.append('email', $('#wiz_email').val());
            formData.append('phone', $('#wiz_phone').val());
            formData.append('gender', $('#wiz_gender').val());
            formData.append('organization_id', $('#wiz_org').val());
            formData.append('department_id', $('#wiz_dept').val());
            formData.append('section_id', $('#wiz_sect').val());
            formData.append('role', $('#wiz_role').val());
            formData.append('member_id', $('#wiz_member_id').val());
            formData.append('card_uid', capturedUid);
            
            let photoFile = $('#wiz_photo')[0].files[0];
            if (photoFile) {
                formData.append('photo', photoFile);
            }

            fetch(apiUrl, { method: 'POST', body: formData }).then(r => r.json()).then(d => {
                if(d.success) {
                    $('#wizardModal').modal('hide');
                    switchView('users');
                    setMode('auth_mod'); // Switch back to auth mode
                } else alert('Error: ' + d.error);
            });
        }

        // --- Settings & Mode ---
        function setMode(m) {
            fetch(apiUrl, { method: 'POST', body: JSON.stringify({action:'set_mode', mode:m}) }).then(r => r.json()).then(d => {
                if(d.success) console.log('System mode: ' + m);
            });
        }

        function loadSettings() {
            fetch(apiUrl + '?action=get_settings').then(r => r.json()).then(s => {
                $('#set_max_access').val(s.max_access_per_day);
                $('#set_max_failed').val(s.max_failed_attempts);
                if(s.system_mode === 'auth_mod') $('#mode_auth').prop('checked', true);
                else $('#mode_reg').prop('checked', true);
            });
        }

        function saveAdvancedSettings() {
            let data = { action: 'save_advanced_settings', max_access_per_day: $('#set_max_access').val(), max_failed_attempts: $('#set_max_failed').val() };
            fetch(apiUrl, { method: 'POST', body: JSON.stringify(data) }).then(r => r.json()).then(d => { if(d.success) alert('Settings saved.'); });
        }

        // --- Management Helpers ---
        function addOrg() {
            let name = $('#new_org_name').val();
            if(!name) return;
            fetch(apiUrl, { method: 'POST', body: JSON.stringify({action:'add_org', name:name}) }).then(r => r.json()).then(d => {
                $('#addOrgModal').modal('hide'); $('#new_org_name').val(''); loadOrgs(); loadOrgsForWizard();
            });
        }

        function openAddDept(orgId) { $('#dept_org_id').val(orgId); $('#addDeptModal').modal('show'); }
        function addDept() {
            let name = $('#new_dept_name').val();
            let orgId = $('#dept_org_id').val();
            fetch(apiUrl, { method: 'POST', body: JSON.stringify({action:'add_dept', name:name, org_id:orgId}) }).then(r => r.json()).then(d => {
                $('#addDeptModal').modal('hide'); $('#new_dept_name').val(''); loadDepts(orgId);
            });
        }

        function openAddSection(deptId) { $('#sect_dept_id').val(deptId); $('#addSectionModal').modal('show'); }
        function addSection() {
            let name = $('#new_sect_name').val();
            let deptId = $('#sect_dept_id').val();
            fetch(apiUrl, { method: 'POST', body: JSON.stringify({action:'add_section', name:name, dept_id:deptId}) }).then(r => r.json()).then(d => {
                $('#addSectionModal').modal('hide'); $('#new_sect_name').val(''); loadSections(deptId);
            });
        }

        function openEditDevice(d) {
            $('#edit_dev_id').val(d.id);
            $('#edit_dev_name').val(d.name);
            $('#edit_dev_loc').val(d.location);
            $('#edit_dev_org').val(d.organization_id || '');
            $('#editDeviceModal').modal('show');
        }

        function saveDevice() {
            let data = {
                action: 'update_device',
                id: $('#edit_dev_id').val(),
                name: $('#edit_dev_name').val(),
                organization_id: $('#edit_dev_org').val(),
                location: $('#edit_dev_loc').val()
            };
            fetch(apiUrl, { method: 'POST', body: JSON.stringify(data) }).then(r => r.json()).then(d => {
                $('#editDeviceModal').modal('hide'); loadDevices();
            });
        }

        function freezeUser(id) { fetch(apiUrl, { method: 'POST', body: JSON.stringify({action:'freeze_user', id:id}) }).then(r => r.json()).then(d => loadUsers()); }
        function unfreezeUser(id) { fetch(apiUrl, { method: 'POST', body: JSON.stringify({action:'unfreeze_user', id:id}) }).then(r => r.json()).then(d => loadUsers()); }
        function deleteUser(id) { if(confirm('Delete member?')) fetch(apiUrl, { method: 'POST', body: JSON.stringify({action:'delete_user', id:id}) }).then(r => r.json()).then(d => loadUsers()); }

    </script>
</body>
</html>
