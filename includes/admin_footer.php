    <!-- Sticky Footer for Admin -->
    <?php
    // Footer Data Logic
    $footerData = [];
    $curMonth = date('n');
    $curYear = date('Y');
    if ($curMonth >= 4) {
        if ($curYear + 1 == 2000) { $yPart = '00'; } else { $yPart = substr($curYear + 1, -2); }
        $footerData['fy'] = $curYear . '-' . $yPart;
    } else {
        $yPart = substr($curYear, -2);
        $footerData['fy'] = ($curYear - 1) . '-' . $yPart;
    }

    if (!empty($brandingSettings)) {
        $footerData['company_name'] = $brandingSettings['app_name'] ?? 'Acculynce Systems';
        $footerData['gst'] = $brandingSettings['gstin'] ?? 'Not Set';
        $footerData['state'] = $brandingSettings['state'] ?? 'Delhi'; 
    } else {
        $footerData['company_name'] = 'Acculynce Systems';
        $footerData['gst'] = 'N/A';
        $footerData['state'] = 'N/A';
    }
    
    // Admin usually manages the platform, so "License" is N/A or "Unlimited"
    $footerData['validity'] = 'Unlimited (Super Admin)';
    $footerData['company_id'] = 'ADMIN';
    ?>
    <style>
        /* Footer Layout Admin Override */
        .sticky-footer-admin {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 48px; 
            background-color: #1e1e2d; /* Dark for Admin */
            display: flex;
            border-top: 1px solid #2b2b40;
            z-index: 1050;
            font-family: 'Inter', sans-serif;
            font-size: 11px;
            color: #a2a3b7;
            box-shadow: 0 -4px 6px -1px rgba(0, 0, 0, 0.2);
        }

        .footer-box-admin {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 0 16px;
            border-right: 1px solid #2b2b40;
            white-space: nowrap;
        }

        .footer-box-admin:last-child {
            border-right: none;
            margin-left: auto;
            background-color: #1b1b28;
            min-width: 220px;
        }

        .brand-box-admin {
            background-color: #1b1b28;
            color: white;
            flex-direction: row;
            align-items: center;
            gap: 12px;
            min-width: 160px;
        }
        
        .footer-logo-admin { max-height: 28px; }
        .footer-brand-text-admin { border: 1px solid #a2a3b7; padding: 2px 4px; color: white; }

        .info-box-admin {
            flex: 2;
            border-left: 4px solid #3699ff;
        }
        .info-row.main { font-weight: 700; font-size: 13px; color: #ffffff; margin-bottom: 2px; }
        .info-row.sub { color: #7e8299; font-size: 11px; }

        .fy-box-admin, .user-box-admin {
            background-color: #1e1e2d;
            min-width: 180px;
            justify-content: space-evenly;
            padding: 4px 16px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: center;
        }
        .label { color: #5e6278; font-weight: 500; }
        .value { font-weight: 600; color: #cdcdde; }
        
        .date-row .value { color: #ffffff; }

        body { padding-bottom: 50px !important; }
    </style>

    <div class="sticky-footer-admin">
        <!-- Box 1: Brand -->
        <div class="footer-box-admin brand-box-admin">
            <?php if (!empty($brandingSettings['logo_path'])): ?>
                <img src="<?php echo BASE_URL . $brandingSettings['logo_path']; ?>" alt="Logo" class="footer-logo-admin">
            <?php else: ?>
                <div class="footer-brand-text-admin">AC</div>
            <?php endif; ?>
            <div class="brand-details">
                <span style="font-weight: 800; font-size: 11px; color: #3699ff; text-transform: uppercase;">Acculynce ERP</span>
            </div>
        </div>

        <!-- Box 2: Info -->
        <div class="footer-box-admin info-box-admin">
            <div class="info-row main">
                <?php echo htmlspecialchars($footerData['company_name']); ?>
            </div>
            <div class="info-row sub">
                (ADMIN PANEL) - Super Admin Console
            </div>
        </div>

        <!-- Box 3: FY -->
        <div class="footer-box-admin fy-box-admin">
            <div class="info-row">
                <span class="label">F.Y. :</span> <span class="value"><?php echo $footerData['fy']; ?></span>
            </div>
            <div class="info-row">
                <span class="label">GSTIN :</span> <span class="value"><?php echo htmlspecialchars($footerData['gst']); ?></span>
            </div>
        </div>
        
        <!-- Box 4: User -->
        <div class="footer-box-admin user-box-admin">
            <div class="info-row">
                <span class="label">User :</span> <span class="value"><?php echo htmlspecialchars($currentUser['full_name']); ?></span>
            </div>
            <div class="info-row">
                <span class="label">Role :</span> <span class="value">Super Admin</span>
            </div>
        </div>

        <!-- Box 5: Date -->
        <div class="footer-box-admin">
           <div class="info-row">
                <span class="label">System Status :</span> <span class="value" style="color: #50cd89;">Active</span>
            </div>
            <div class="info-row date-row">
                <span class="value"><?php echo date('l, d-m-Y'); ?></span>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
