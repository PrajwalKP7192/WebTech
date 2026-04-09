<?php
// K2 Climbing Permit Application - PHP Processing

$errors = [];
$success = false;
$submitted_data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- Personal Info Validation ---
    $full_name     = trim($_POST['full_name'] ?? '');
    $dob           = trim($_POST['dob'] ?? '');
    $nationality   = trim($_POST['nationality'] ?? '');
    $passport_no   = trim($_POST['passport_no'] ?? '');
    $passport_exp  = trim($_POST['passport_exp'] ?? '');
    $email         = trim($_POST['email'] ?? '');
    $phone         = trim($_POST['phone'] ?? '');

    // --- Expedition Info ---
    $expedition_name  = trim($_POST['expedition_name'] ?? '');
    $climb_season     = trim($_POST['climb_season'] ?? '');
    $climb_year       = trim($_POST['climb_year'] ?? '');
    $route            = trim($_POST['route'] ?? '');
    $team_size        = trim($_POST['team_size'] ?? '');
    $base_camp_date   = trim($_POST['base_camp_date'] ?? '');
    $summit_date      = trim($_POST['summit_date'] ?? '');

    // --- Experience ---
    $experience       = trim($_POST['experience'] ?? '');
    $prev_expeditions = trim($_POST['prev_expeditions'] ?? '');
    $has_insurance    = isset($_POST['has_insurance']) ? 'Yes' : 'No';
    $insurance_no     = trim($_POST['insurance_no'] ?? '');

    // --- Emergency Contact ---
    $emg_name         = trim($_POST['emg_name'] ?? '');
    $emg_relation     = trim($_POST['emg_relation'] ?? '');
    $emg_phone        = trim($_POST['emg_phone'] ?? '');
    $emg_email        = trim($_POST['emg_email'] ?? '');

    // --- Validate Required Fields ---
    if (empty($full_name))      $errors[] = "Full name is required.";
    if (empty($dob))            $errors[] = "Date of birth is required.";
    if (empty($nationality))    $errors[] = "Nationality is required.";
    if (empty($passport_no))    $errors[] = "Passport number is required.";
    if (empty($passport_exp))   $errors[] = "Passport expiry is required.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL))
                                $errors[] = "A valid email address is required.";
    if (empty($phone))          $errors[] = "Phone number is required.";
    if (empty($expedition_name))$errors[] = "Expedition name is required.";
    if (empty($climb_season))   $errors[] = "Climbing season is required.";
    if (empty($climb_year))     $errors[] = "Climbing year is required.";
    if (empty($route))          $errors[] = "Route is required.";
    if (empty($team_size) || !is_numeric($team_size) || $team_size < 1)
                                $errors[] = "Valid team size is required.";
    if (empty($base_camp_date)) $errors[] = "Base camp arrival date is required.";
    if (empty($experience))     $errors[] = "Experience level is required.";
    if (empty($emg_name))       $errors[] = "Emergency contact name is required.";
    if (empty($emg_phone))      $errors[] = "Emergency contact phone is required.";
    if ($has_insurance === 'Yes' && empty($insurance_no))
                                $errors[] = "Insurance policy number is required when insured.";

    if (empty($errors)) {
        $success = true;
        $permit_ref = 'K2-' . strtoupper(substr(md5(time() . $full_name), 0, 8));
        $submitted_data = compact(
            'full_name','dob','nationality','passport_no','passport_exp',
            'email','phone','expedition_name','climb_season','climb_year',
            'route','team_size','base_camp_date','summit_date','experience',
            'prev_expeditions','has_insurance','insurance_no',
            'emg_name','emg_relation','emg_phone','emg_email','permit_ref'
        );
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>K2 Climbing Permit Application</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700;900&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --ice:       #c8dff0;
            --sky:       #7ab3d4;
            --deep:      #0d2137;
            --midnight:  #07111e;
            --gold:      #c9a84c;
            --gold-light:#e8cc80;   
            --snow:      #f0f4f8;
            --danger:    #e05050;
            --success:   #3cba6e;
            --text:      #d6e4f0;
            --muted:     #6a8ba8;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Lato', sans-serif;
            background-color: var(--midnight);
            color: var(--text);
            min-height: 100vh;
            background-image:
                radial-gradient(ellipse at 20% 0%, rgba(13,33,55,0.9) 0%, transparent 60%),
                radial-gradient(ellipse at 80% 100%, rgba(7,17,30,0.95) 0%, transparent 60%),
                url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='800' height='400' viewBox='0 0 800 400'%3E%3Cpolygon points='0,400 200,80 350,220 500,40 650,180 800,60 800,400' fill='%230d2137' opacity='0.7'/%3E%3Cpolygon points='0,400 150,150 300,280 450,100 600,230 750,120 800,400' fill='%23071525' opacity='0.8'/%3E%3C/svg%3E");
            background-size: cover;
            background-attachment: fixed;
        }

        /* ── HERO HEADER ── */
        .hero {
            text-align: center;
            padding: 60px 20px 40px;
            position: relative;
        }
        .hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(201,168,76,0.08) 0%, transparent 100%);
            pointer-events: none;
        }
        .hero-badge {
            display: inline-block;
            font-family: 'Lato', sans-serif;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.35em;
            text-transform: uppercase;
            color: var(--gold);
            border: 1px solid rgba(201,168,76,0.4);
            padding: 6px 18px;
            border-radius: 2px;
            margin-bottom: 20px;
        }
        .hero h1 {
            font-family: 'Cinzel', serif;
            font-size: clamp(2rem, 5vw, 3.8rem);
            font-weight: 900;
            color: var(--snow);
            letter-spacing: 0.06em;
            line-height: 1.1;
            text-shadow: 0 2px 30px rgba(201,168,76,0.25);
        }
        .hero h1 span { color: var(--gold-light); }
        .hero-sub {
            font-size: 0.9rem;
            color: var(--muted);
            letter-spacing: 0.15em;
            text-transform: uppercase;
            margin-top: 12px;
        }
        .hero-divider {
            width: 80px;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--gold), transparent);
            margin: 24px auto 0;
        }

        /* ── CONTAINER ── */
        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px 20px 60px;
        }

        /* ── ALERT BOXES ── */
        .alert {
            border-radius: 4px;
            padding: 16px 20px;
            margin-bottom: 28px;
            font-size: 0.88rem;
            line-height: 1.6;
        }
        .alert-error {
            background: rgba(224,80,80,0.12);
            border-left: 3px solid var(--danger);
            color: #f5a0a0;
        }
        .alert-error ul { padding-left: 18px; margin-top: 6px; }
        .alert-success {
            background: rgba(60,186,110,0.1);
            border-left: 3px solid var(--success);
            color: #90e0b0;
        }

        /* ── SUCCESS CARD ── */
        .success-card {
            background: rgba(13,33,55,0.7);
            border: 1px solid rgba(201,168,76,0.2);
            border-radius: 6px;
            padding: 36px;
            margin-bottom: 32px;
        }
        .success-card h2 {
            font-family: 'Cinzel', serif;
            color: var(--gold-light);
            margin-bottom: 20px;
            font-size: 1.3rem;
            letter-spacing: 0.08em;
        }
        .ref-badge {
            display: inline-block;
            font-family: 'Cinzel', serif;
            font-size: 1.5rem;
            color: var(--gold);
            background: rgba(201,168,76,0.1);
            border: 1px solid rgba(201,168,76,0.35);
            padding: 10px 24px;
            border-radius: 4px;
            letter-spacing: 0.12em;
            margin-bottom: 24px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 12px;
        }
        .summary-item { font-size: 0.85rem; }
        .summary-item .label { color: var(--muted); font-size: 0.72rem; letter-spacing: 0.1em; text-transform: uppercase; margin-bottom: 3px; }
        .summary-item .value { color: var(--snow); }

        /* ── FORM CARD ── */
        .form-card {
            background: rgba(13,33,55,0.65);
            border: 1px solid rgba(122,179,212,0.12);
            border-radius: 6px;
            overflow: hidden;
            margin-bottom: 20px;
            backdrop-filter: blur(6px);
        }
        .form-card-header {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 18px 28px;
            background: rgba(201,168,76,0.07);
            border-bottom: 1px solid rgba(201,168,76,0.15);
        }
        .section-num {
            width: 28px; height: 28px;
            border-radius: 50%;
            border: 1px solid var(--gold);
            display: flex; align-items: center; justify-content: center;
            font-family: 'Cinzel', serif;
            font-size: 0.75rem;
            color: var(--gold);
            flex-shrink: 0;
        }
        .form-card-header h2 {
            font-family: 'Cinzel', serif;
            font-size: 0.95rem;
            font-weight: 600;
            letter-spacing: 0.1em;
            color: var(--ice);
        }
        .form-body { padding: 28px; }

        /* ── GRID LAYOUT ── */
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
        .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 18px; }
        .col-span-2 { grid-column: span 2; }
        .col-span-3 { grid-column: span 3; }

        @media (max-width: 640px) {
            .grid-2, .grid-3 { grid-template-columns: 1fr; }
            .col-span-2, .col-span-3 { grid-column: span 1; }
        }

        /* ── FORM FIELDS ── */
        .field { display: flex; flex-direction: column; gap: 7px; }
        label {
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--muted);
        }
        label .req { color: var(--gold); margin-left: 3px; }

        input[type="text"],
        input[type="email"],
        input[type="tel"],
        input[type="date"],
        input[type="number"],
        select,
        textarea {
            width: 100%;
            background: rgba(7,17,30,0.6);
            border: 1px solid rgba(122,179,212,0.2);
            border-radius: 3px;
            padding: 11px 14px;
            font-family: 'Lato', sans-serif;
            font-size: 0.88rem;
            color: var(--snow);
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            -webkit-appearance: none;
        }
        input:focus, select:focus, textarea:focus {
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(201,168,76,0.12);
        }
        select option { background: var(--deep); }
        textarea { resize: vertical; min-height: 90px; }

        /* ── CHECKBOX ── */
        .checkbox-field {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px;
            background: rgba(7,17,30,0.4);
            border: 1px solid rgba(122,179,212,0.15);
            border-radius: 3px;
            cursor: pointer;
        }
        .checkbox-field input[type="checkbox"] {
            width: 18px; height: 18px;
            accent-color: var(--gold);
            cursor: pointer;
        }
        .checkbox-field span {
            font-size: 0.85rem;
            color: var(--text);
        }

        /* ── DECLARATION ── */
        .declaration {
            background: rgba(201,168,76,0.05);
            border: 1px solid rgba(201,168,76,0.2);
            border-radius: 4px;
            padding: 20px 24px;
            font-size: 0.82rem;
            color: var(--muted);
            line-height: 1.7;
            margin-bottom: 24px;
        }
        .declaration strong { color: var(--gold-light); }

        /* ── SUBMIT BUTTON ── */
        .btn-submit {
            width: 100%;
            padding: 16px;
            font-family: 'Cinzel', serif;
            font-size: 0.95rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: var(--midnight);
            background: linear-gradient(135deg, var(--gold-light), var(--gold));
            border: none;
            border-radius: 3px;
            cursor: pointer;
            transition: opacity 0.2s, transform 0.15s;
        }
        .btn-submit:hover { opacity: 0.9; transform: translateY(-1px); }
        .btn-submit:active { transform: translateY(0); }

        /* ── FOOTER NOTE ── */
        .footer-note {
            text-align: center;
            font-size: 0.73rem;
            color: var(--muted);
            letter-spacing: 0.08em;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid rgba(122,179,212,0.1);
        }
    </style>
</head>
<body>
<div style="max-width:1100px;margin:20px auto 0;padding:0 20px;">
    <a href="index.php" style="display:inline-flex;align-items:center;justify-content:center;min-height:42px;padding:0 18px;border-radius:999px;text-decoration:none;background:rgba(201,168,76,0.12);color:#e8cc80;border:1px solid rgba(201,168,76,0.28);font-family:'Lato',sans-serif;font-size:0.82rem;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;">
        Back to K2 Portal
    </a>
</div>

<!-- ── HERO ── -->
<header class="hero">
    <div class="hero-badge">Pakistan Alpine Club &bull; Official Portal</div>
    <h1>K<span>2</span> SUMMIT PERMIT</h1>
    <p class="hero-sub">Karakoram Range &bull; 8,611 m &bull; Second Highest Peak on Earth</p>
    <div class="hero-divider"></div>
</header>

<div class="container">

    <!-- SUCCESS MESSAGE -->
    <?php if ($success): ?>
    <div class="alert alert-success">
        ✔ Your permit application has been successfully submitted. Keep your reference number for tracking.
    </div>
    <div class="success-card">
        <h2>Application Summary</h2>
        <div class="ref-badge"><?= htmlspecialchars($submitted_data['permit_ref']) ?></div>
        <div class="summary-grid">
            <div class="summary-item"><div class="label">Applicant</div><div class="value"><?= htmlspecialchars($submitted_data['full_name']) ?></div></div>
            <div class="summary-item"><div class="label">Nationality</div><div class="value"><?= htmlspecialchars($submitted_data['nationality']) ?></div></div>
            <div class="summary-item"><div class="label">Passport No</div><div class="value"><?= htmlspecialchars($submitted_data['passport_no']) ?></div></div>
            <div class="summary-item"><div class="label">Email</div><div class="value"><?= htmlspecialchars($submitted_data['email']) ?></div></div>
            <div class="summary-item"><div class="label">Expedition</div><div class="value"><?= htmlspecialchars($submitted_data['expedition_name']) ?></div></div>
            <div class="summary-item"><div class="label">Season / Year</div><div class="value"><?= htmlspecialchars($submitted_data['climb_season']) ?> <?= htmlspecialchars($submitted_data['climb_year']) ?></div></div>
            <div class="summary-item"><div class="label">Route</div><div class="value"><?= htmlspecialchars($submitted_data['route']) ?></div></div>
            <div class="summary-item"><div class="label">Team Size</div><div class="value"><?= htmlspecialchars($submitted_data['team_size']) ?> members</div></div>
            <div class="summary-item"><div class="label">Insured</div><div class="value"><?= htmlspecialchars($submitted_data['has_insurance']) ?></div></div>
            <div class="summary-item"><div class="label">Emergency Contact</div><div class="value"><?= htmlspecialchars($submitted_data['emg_name']) ?></div></div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ERROR MESSAGE -->
    <?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <strong>Please correct the following errors:</strong>
        <ul>
            <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <!-- ── FORM ── -->
    <?php if (!$success): ?>
    <form method="POST" action="" novalidate>

        <!-- SECTION 1: Personal Information -->
        <div class="form-card">
            <div class="form-card-header">
                <div class="section-num">1</div>
                <h2>Personal Information</h2>
            </div>
            <div class="form-body">
                <div class="grid-2">
                    <div class="field col-span-2">
                        <label>Full Name (as on passport) <span class="req">*</span></label>
                        <input type="text" name="full_name" placeholder="John Alexander Smith" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
                    </div>
                    <div class="field">
                        <label>Date of Birth <span class="req">*</span></label>
                        <input type="date" name="dob" value="<?= htmlspecialchars($_POST['dob'] ?? '') ?>">
                    </div>
                    <div class="field">
                        <label>Nationality <span class="req">*</span></label>
                        <input type="text" name="nationality" placeholder="e.g. Italian" value="<?= htmlspecialchars($_POST['nationality'] ?? '') ?>">
                    </div>
                    <div class="field">
                        <label>Passport Number <span class="req">*</span></label>
                        <input type="text" name="passport_no" placeholder="e.g. AB1234567" value="<?= htmlspecialchars($_POST['passport_no'] ?? '') ?>">
                    </div>
                    <div class="field">
                        <label>Passport Expiry Date <span class="req">*</span></label>
                        <input type="date" name="passport_exp" value="<?= htmlspecialchars($_POST['passport_exp'] ?? '') ?>">
                    </div>
                    <div class="field">
                        <label>Email Address <span class="req">*</span></label>
                        <input type="email" name="email" placeholder="climber@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                    <div class="field">
                        <label>Phone (with country code) <span class="req">*</span></label>
                        <input type="tel" name="phone" placeholder="+1 555 000 0000" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 2: Expedition Details -->
        <div class="form-card">
            <div class="form-card-header">
                <div class="section-num">2</div>
                <h2>Expedition Details</h2>
            </div>
            <div class="form-body">
                <div class="grid-3">
                    <div class="field col-span-3">
                        <label>Expedition / Team Name <span class="req">*</span></label>
                        <input type="text" name="expedition_name" placeholder="e.g. Alpine Pioneers 2026" value="<?= htmlspecialchars($_POST['expedition_name'] ?? '') ?>">
                    </div>
                    <div class="field">
                        <label>Climbing Season <span class="req">*</span></label>
                        <select name="climb_season">
                            <option value="">Select season</option>
                            <?php foreach (['Spring (Apr–Jun)','Summer (Jul–Aug)','Autumn (Sep–Oct)'] as $s): ?>
                            <option value="<?= $s ?>" <?= (($_POST['climb_season'] ?? '') === $s) ? 'selected' : '' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label>Year <span class="req">*</span></label>
                        <select name="climb_year">
                            <option value="">Select year</option>
                            <?php for ($y = 2026; $y <= 2030; $y++): ?>
                            <option value="<?= $y ?>" <?= (($_POST['climb_year'] ?? '') == $y) ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label>Team Size <span class="req">*</span></label>
                        <input type="number" name="team_size" min="1" max="50" placeholder="e.g. 4" value="<?= htmlspecialchars($_POST['team_size'] ?? '') ?>">
                    </div>
                    <div class="field col-span-3">
                        <label>Proposed Route <span class="req">*</span></label>
                        <select name="route">
                            <option value="">Select route</option>
                            <?php foreach ([
                                'Abruzzi Spur (South-East Ridge)',
                                'North Ridge (Chinese Side)',
                                'West Ridge',
                                'South Face',
                                'Magic Line (South-South-West Ridge)',
                                'Cesen Route (South-South-East Spur)',
                                'Northwest Ridge',
                                'Other / New Route'
                            ] as $r): ?>
                            <option value="<?= $r ?>" <?= (($_POST['route'] ?? '') === $r) ? 'selected' : '' ?>><?= $r ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label>Base Camp Arrival</label>
                        <input type="date" name="base_camp_date" value="<?= htmlspecialchars($_POST['base_camp_date'] ?? '') ?>">
                    </div>
                    <div class="field">
                        <label>Planned Summit Date</label>
                        <input type="date" name="summit_date" value="<?= htmlspecialchars($_POST['summit_date'] ?? '') ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 3: Climbing Experience -->
        <div class="form-card">
            <div class="form-card-header">
                <div class="section-num">3</div>
                <h2>Climbing Experience</h2>
            </div>
            <div class="form-body">
                <div class="grid-2">
                    <div class="field col-span-2">
                        <label>Experience Level <span class="req">*</span></label>
                        <select name="experience">
                            <option value="">Select level</option>
                            <?php foreach ([
                                'Advanced – 8000m summits achieved',
                                'Intermediate – 7000m peaks, high-altitude experience',
                                'Beginner – Below 7000m, guided only'
                            ] as $lvl): ?>
                            <option value="<?= $lvl ?>" <?= (($_POST['experience'] ?? '') === $lvl) ? 'selected' : '' ?>><?= $lvl ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field col-span-2">
                        <label>Previous High-Altitude Expeditions</label>
                        <textarea name="prev_expeditions" placeholder="List your previous major expeditions, peaks climbed, and years..."><?= htmlspecialchars($_POST['prev_expeditions'] ?? '') ?></textarea>
                    </div>
                    <div class="field col-span-2">
                        <label class="checkbox-field">
                            <input type="checkbox" name="has_insurance" <?= isset($_POST['has_insurance']) ? 'checked' : '' ?>>
                            <span>I have high-altitude rescue and medical insurance coverage</span>
                        </label>
                    </div>
                    <div class="field col-span-2">
                        <label>Insurance Policy Number (if insured)</label>
                        <input type="text" name="insurance_no" placeholder="e.g. INS-2026-XXXXXXXX" value="<?= htmlspecialchars($_POST['insurance_no'] ?? '') ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 4: Emergency Contact -->
        <div class="form-card">
            <div class="form-card-header">
                <div class="section-num">4</div>
                <h2>Emergency Contact</h2>
            </div>
            <div class="form-body">
                <div class="grid-2">
                    <div class="field">
                        <label>Contact Name <span class="req">*</span></label>
                        <input type="text" name="emg_name" placeholder="Full name" value="<?= htmlspecialchars($_POST['emg_name'] ?? '') ?>">
                    </div>
                    <div class="field">
                        <label>Relationship</label>
                        <select name="emg_relation">
                            <option value="">Select</option>
                            <?php foreach (['Spouse','Parent','Sibling','Friend','Team Leader','Other'] as $rel): ?>
                            <option value="<?= $rel ?>" <?= (($_POST['emg_relation'] ?? '') === $rel) ? 'selected' : '' ?>><?= $rel ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label>Phone <span class="req">*</span></label>
                        <input type="tel" name="emg_phone" placeholder="+1 555 000 0000" value="<?= htmlspecialchars($_POST['emg_phone'] ?? '') ?>">
                    </div>
                    <div class="field">
                        <label>Email</label>
                        <input type="email" name="emg_email" placeholder="contact@example.com" value="<?= htmlspecialchars($_POST['emg_email'] ?? '') ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- DECLARATION -->
        <div class="declaration">
            <strong>Declaration:</strong> I, the undersigned, hereby declare that all information provided in this application is true and accurate to the best of my knowledge. I acknowledge the extreme risks associated with climbing K2, confirm I am medically fit to undertake this expedition, and agree to comply with all regulations set forth by the <strong>Pakistan Alpine Club</strong> and the <strong>Ministry of Tourism, Pakistan</strong>. I accept full personal responsibility for my safety and the safety of my team.
        </div>

        <button type="submit" class="btn-submit">Submit Permit Application</button>

    </form>
    <?php endif; ?>

    <p class="footer-note">
        Pakistan Alpine Club &bull; Alpine Wing, Ministry of Tourism &bull; Islamabad, Pakistan<br>
        For queries: permits@alpineclub.pk &bull; +92 51 000 0000
    </p>

</div>
</body>
</html> 
