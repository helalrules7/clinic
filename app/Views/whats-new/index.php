<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>What's New in v6.0 - Roaya Clinic</title>
    
    <!-- Meta Description -->
    <meta name="description" content="Discover the new features and improvements in HClinic / Roaya Clinic v6.0">
    <meta name="keywords" content="clinic, eye care, ophthalmology, medical, healthcare, update, features">
    <meta name="author" content="Ahmed Helal">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://roaya.hclinic.clinic/whats-new">
    <meta property="og:title" content="What's New in v6.0 - Roaya Clinic">
    <meta property="og:description" content="Discover the new features and improvements in HClinic / Roaya Clinic v6.0">
    <meta property="og:image" content="https://roaya.hclinic.clinic/assets/images/Light.png">
    <meta property="og:site_name" content="HClinic / Roaya Clinic">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="https://roaya.hclinic.clinic/whats-new">
    <meta name="twitter:title" content="What's New in v6.0 - Roaya Clinic">
    <meta name="twitter:description" content="Discover the new features and improvements in HClinic / Roaya Clinic v6.0">
    <meta name="twitter:image" content="https://roaya.hclinic.clinic/assets/images/Light.png">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <!-- Cairo Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Favicons -->
    <link id="favicon" rel="icon" type="image/png" sizes="32x32" href="/assets/images/Light.png">
    <link id="favicon-dark" rel="icon" type="image/png" sizes="32x32" href="/assets/images/Dark.png" media="(prefers-color-scheme: dark)">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/images/Light.png">
    
    <style>
        :root {
            --bg-light: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --bg-dark: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            --card-light: #ffffff;
            --card-dark: #1e293b;
            --text-light: #2c3e50;
            --text-dark: #f8fafc;
            --subtitle-light: #6c757d;
            --subtitle-dark: #cbd5e1;
            --accent-light: #667eea;
            --accent-dark: #38bdf8;
            --shadow-light: rgba(0, 0, 0, 0.1);
            --shadow-dark: rgba(0, 0, 0, 0.5);
            --success-light: #10b981;
            --success-dark: #34d399;
        }
        
        * {
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
        }
        
        body {
            background: var(--bg-light);
            min-height: 100vh;
            font-family: 'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 2rem 1rem;
            color: var(--text-light);
        }
        
        @media (prefers-color-scheme: dark) {
            body {
                background: var(--bg-dark);
                color: var(--text-dark);
            }
        }
        
        body.dark {
            background: var(--bg-dark);
            color: var(--text-dark);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            text-align: center;
            margin-bottom: 4rem;
            padding: 2rem 0;
        }
        
        .logo {
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .logo img {
            max-width: 120px;
            height: auto;
            transition: transform 0.3s ease;
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.2));
        }
        
        .logo img:hover {
            transform: scale(1.1);
        }
        
        @media (prefers-color-scheme: dark) {
            .logo img {
                filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.5));
            }
        }
        
        body.dark .logo img {
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.5));
        }
        
        .page-title {
            font-size: 3rem;
            font-weight: bold;
            color: var(--text-light);
            margin-bottom: 0.5rem;
        }
        
        @media (prefers-color-scheme: dark) {
            .page-title {
                color: var(--text-dark);
            }
        }
        
        body.dark .page-title {
            color: var(--text-dark);
        }
        
        .page-subtitle {
            font-size: 1.3rem;
            color: var(--subtitle-light);
            margin-bottom: 2rem;
        }
        
        @media (prefers-color-scheme: dark) {
            .page-subtitle {
                color: var(--subtitle-dark);
            }
        }
        
        body.dark .page-subtitle {
            color: var(--subtitle-dark);
        }
        
        .version-badge {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        
        @media (prefers-color-scheme: dark) {
            .version-badge {
                background: linear-gradient(135deg, #38bdf8 0%, #0ea5e9 100%);
                box-shadow: 0 4px 12px rgba(56, 189, 248, 0.3);
            }
        }
        
        body.dark .version-badge {
            background: linear-gradient(135deg, #38bdf8 0%, #0ea5e9 100%);
            box-shadow: 0 4px 12px rgba(56, 189, 248, 0.3);
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }
        
        @media (max-width: 1200px) {
            .features-grid {
                grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            }
        }
        
        @media (max-width: 900px) {
            .features-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .feature-card.full-width {
            grid-column: 1 / -1;
        }
        
        @media (max-width: 900px) {
            .feature-card.full-width {
                grid-column: 1;
            }
        }
        
        .feature-card {
            background: var(--card-light);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px var(--shadow-light);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        
        .feature-card:hover::before {
            transform: scaleX(1);
        }
        
        @media (prefers-color-scheme: dark) {
            .feature-card {
                background: var(--card-dark);
                box-shadow: 0 10px 30px var(--shadow-dark);
                border-color: rgba(255, 255, 255, 0.1);
            }
            
            .feature-card::before {
                background: linear-gradient(90deg, #38bdf8 0%, #0ea5e9 100%);
            }
        }
        
        body.dark .feature-card {
            background: var(--card-dark);
            box-shadow: 0 10px 30px var(--shadow-dark);
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        body.dark .feature-card::before {
            background: linear-gradient(90deg, #38bdf8 0%, #0ea5e9 100%);
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px var(--shadow-light);
        }
        
        @media (prefers-color-scheme: dark) {
            .feature-card:hover {
                box-shadow: 0 15px 40px var(--shadow-dark);
            }
        }
        
        body.dark .feature-card:hover {
            box-shadow: 0 15px 40px var(--shadow-dark);
        }
        
        .feature-icon {
            font-size: 3.5rem;
            color: var(--accent-light);
            margin-bottom: 1rem;
            display: block;
        }
        
        @media (prefers-color-scheme: dark) {
            .feature-icon {
                color: var(--accent-dark);
            }
        }
        
        body.dark .feature-icon {
            color: var(--accent-dark);
        }
        
        .feature-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--text-light);
            margin-bottom: 1rem;
        }
        
        @media (prefers-color-scheme: dark) {
            .feature-title {
                color: var(--text-dark);
            }
        }
        
        body.dark .feature-title {
            color: var(--text-dark);
        }
        
        .feature-description {
            color: var(--subtitle-light);
            line-height: 1.6;
            margin-bottom: 1rem;
        }
        
        @media (prefers-color-scheme: dark) {
            .feature-description {
                color: var(--subtitle-dark);
            }
        }
        
        body.dark .feature-description {
            color: var(--subtitle-dark);
        }
        
        .feature-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .feature-list li {
            color: var(--subtitle-light);
            padding: 0.5rem 0;
            padding-left: 1.5rem;
            position: relative;
        }
        
        .feature-list li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: var(--success-light);
            font-weight: bold;
        }
        
        @media (prefers-color-scheme: dark) {
            .feature-list li {
                color: var(--subtitle-dark);
            }
            
            .feature-list li::before {
                color: var(--success-dark);
            }
        }
        
        body.dark .feature-list li {
            color: var(--subtitle-dark);
        }
        
        body.dark .feature-list li::before {
            color: var(--success-dark);
        }
        
        .feature-mockup {
            width: 100%;
            border-radius: 12px;
            margin-top: 1rem;
            border: 2px solid var(--accent-light);
            position: relative;
            background: var(--card-light);
            box-shadow: inset 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 1rem;
        }
        
        .feature-mockup-inner {
            width: 100%;
            min-height: 500px;
            border-radius: 8px;
            overflow: visible;
            position: relative;
        }
        
        @media (prefers-color-scheme: dark) {
            .feature-mockup {
                background: var(--card-dark);
                border-color: var(--accent-dark);
                box-shadow: inset 0 2px 10px rgba(0, 0, 0, 0.3);
            }
        }
        
        body.dark .feature-mockup {
            background: var(--card-dark);
            border-color: var(--accent-dark);
            box-shadow: inset 0 2px 10px rgba(0, 0, 0, 0.3);
        }
        
        /* Media Gallery Mockup */
        .mockup-media {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            padding: 12px;
            height: 100%;
        }
        
        .mockup-media-item {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 8px;
            position: relative;
            overflow: hidden;
            animation: pulse 2s ease-in-out infinite;
            cursor: pointer;
            aspect-ratio: 1;
            min-height: 60px;
        }
        
        .mockup-media-item:nth-child(1) { animation-delay: 0s; }
        .mockup-media-item:nth-child(2) { animation-delay: 0.3s; }
        .mockup-media-item:nth-child(3) { animation-delay: 0.6s; }
        .mockup-media-item:nth-child(4) { animation-delay: 0.9s; }
        .mockup-media-item:nth-child(5) { animation-delay: 1.2s; }
        .mockup-media-item:nth-child(6) { animation-delay: 1.5s; }
        
        .mockup-media-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.2) 50%, transparent 70%);
            animation: shine 3s infinite;
            z-index: 2;
            pointer-events: none;
        }
        
        .mockup-media-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            top: 0;
            left: 0;
            z-index: 0;
            display: block;
        }
        
        .mockup-media-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, transparent 0%, rgba(0, 0, 0, 0.85) 100%);
            opacity: 1 !important;
            transition: opacity 0.3s ease, background 0.3s ease;
            z-index: 1;
            display: flex;
            align-items: flex-end;
            padding: 8px;
            pointer-events: none;
        }
        
        .mockup-media-overlay * {
            pointer-events: auto;
        }
        
        .mockup-media-item:hover .mockup-media-overlay {
            background: linear-gradient(to bottom, transparent 0%, rgba(0, 0, 0, 0.95) 100%);
        }
        
        .mockup-media-patient-name {
            color: white;
            font-size: 11px;
            font-weight: 600;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8);
            margin: 0;
            padding: 4px 8px;
            background: rgba(0, 0, 0, 0.7);
            border-radius: 4px;
            backdrop-filter: blur(5px);
            width: 100%;
            text-align: center;
            position: relative;
            z-index: 3;
        }
        
        .mockup-media-badge {
            position: absolute;
            top: 6px;
            right: 6px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            z-index: 3;
        }
        
        .mockup-media-item:active {
            transform: scale(0.95);
            animation: none;
        }
        
        /* Media Modal */
        .mockup-media-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.9);
            z-index: 10000;
            align-items: center;
            justify-content: center;
            animation: fadeInModal 0.3s ease;
        }
        
        .mockup-media-modal.active {
            display: flex;
        }
        
        .mockup-media-modal-content {
            max-width: 90%;
            max-height: 90%;
            position: relative;
            animation: scaleInModal 0.3s ease;
        }
        
        .mockup-media-modal-image {
            max-width: 100%;
            max-height: 90vh;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        }
        
        .mockup-media-modal-close {
            position: absolute;
            top: -40px;
            right: 0;
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        
        .mockup-media-modal-close:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.1);
        }
        
        @keyframes fadeInModal {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes scaleInModal {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        
        /* Glasses Prescriptions Mockup */
        .mockup-glasses {
            padding: 12px;
            height: 100%;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .mockup-glasses-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 8px;
            padding: 10px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            animation: slideIn 1s ease-out;
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        
        .mockup-glasses-card:hover {
            transform: translateY(-2px);
        }
        
        .mockup-glasses-card:active {
            transform: scale(0.98);
        }
        
        .mockup-glasses-icon {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
            flex-shrink: 0;
        }
        
        .mockup-glasses-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        
        .mockup-glasses-name {
            height: 12px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 4px;
            width: 60%;
            animation: shimmer 2s infinite;
        }
        
        .mockup-glasses-prescription {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        
        .mockup-glasses-prescription-row {
            display: flex;
            gap: 8px;
            font-size: 9px;
            color: white;
        }
        
        .mockup-glasses-prescription-label {
            font-weight: 600;
            min-width: 35px;
            opacity: 0.9;
        }
        
        .mockup-glasses-prescription-value {
            opacity: 0.8;
        }
        
        /* Glasses Modal */
        .mockup-glasses-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.9);
            z-index: 10000;
            align-items: center;
            justify-content: center;
            animation: fadeInModal 0.3s ease;
        }
        
        .mockup-glasses-modal.active {
            display: flex;
        }
        
        .mockup-glasses-modal-content {
            background: var(--card-light);
            border-radius: 20px;
            padding: 2rem;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            animation: scaleInModal 0.3s ease;
        }
        
        @media (prefers-color-scheme: dark) {
            .mockup-glasses-modal-content {
                background: var(--card-dark);
            }
        }
        
        body.dark .mockup-glasses-modal-content {
            background: var(--card-dark);
        }
        
        .mockup-glasses-modal-close {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(0, 0, 0, 0.1);
            border: none;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-light);
            font-size: 18px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .mockup-glasses-modal-close:hover {
            background: rgba(0, 0, 0, 0.2);
            transform: scale(1.1);
        }
        
        @media (prefers-color-scheme: dark) {
            .mockup-glasses-modal-close {
                color: var(--text-dark);
            }
        }
        
        body.dark .mockup-glasses-modal-close {
            color: var(--text-dark);
        }
        
        .mockup-glasses-modal-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--text-light);
            margin-bottom: 1rem;
        }
        
        @media (prefers-color-scheme: dark) {
            .mockup-glasses-modal-title {
                color: var(--text-dark);
            }
        }
        
        body.dark .mockup-glasses-modal-title {
            color: var(--text-dark);
        }
        
        .mockup-glasses-modal-prescription {
            background: rgba(16, 185, 129, 0.1);
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 1rem;
        }
        
        .mockup-glasses-modal-prescription-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            color: var(--text-light);
        }
        
        .mockup-glasses-modal-prescription-row:last-child {
            border-bottom: none;
        }
        
        @media (prefers-color-scheme: dark) {
            .mockup-glasses-modal-prescription-row {
                color: var(--text-dark);
                border-bottom-color: rgba(255, 255, 255, 0.1);
            }
        }
        
        body.dark .mockup-glasses-modal-prescription-row {
            color: var(--text-dark);
            border-bottom-color: rgba(255, 255, 255, 0.1);
        }
        
        .mockup-glasses-modal-label {
            font-weight: 600;
            color: var(--subtitle-light);
        }
        
        @media (prefers-color-scheme: dark) {
            .mockup-glasses-modal-label {
                color: var(--subtitle-dark);
            }
        }
        
        body.dark .mockup-glasses-modal-label {
            color: var(--subtitle-dark);
        }
        
        .mockup-glasses-modal-value {
            font-weight: 500;
        }
        
        /* Mockup Badge */
        .mockup-badge {
            text-align: center;
            margin-top: 0.75rem;
            padding: 0.5rem 1rem;
            background: rgba(102, 126, 234, 0.1);
            border: 1px solid rgba(102, 126, 234, 0.3);
            border-radius: 20px;
            font-size: 0.85rem;
            color: var(--accent-light);
            font-weight: 500;
            animation: fadeInUp 0.6s ease;
        }
        
        @media (prefers-color-scheme: dark) {
            .mockup-badge {
                background: rgba(56, 189, 248, 0.1);
                border-color: rgba(56, 189, 248, 0.3);
                color: var(--accent-dark);
            }
        }
        
        body.dark .mockup-badge {
            background: rgba(56, 189, 248, 0.1);
            border-color: rgba(56, 189, 248, 0.3);
            color: var(--accent-dark);
        }
        
        /* Medication Prescriptions Mockup */
        .mockup-medications {
            padding: 12px;
            height: 100%;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .mockup-medication-group {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border-radius: 8px;
            padding: 10px;
            animation: expand 1.5s ease-out;
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        
        .mockup-medication-group:hover {
            transform: translateY(-2px);
        }
        
        .mockup-medication-group:active {
            transform: scale(0.98);
        }
        
        .mockup-medication-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        
        .mockup-medication-date {
            height: 10px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 4px;
            width: 50%;
        }
        
        .mockup-medication-items {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        
        .mockup-medication-item {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }
        
        .mockup-medication-drug-name {
            font-size: 10px;
            font-weight: 600;
            color: white;
            opacity: 0.95;
        }
        
        .mockup-medication-drug-details {
            font-size: 9px;
            color: white;
            opacity: 0.85;
            display: flex;
            gap: 8px;
        }
        
        /* Medication Modal */
        .mockup-medication-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.9);
            z-index: 10000;
            align-items: center;
            justify-content: center;
            animation: fadeInModal 0.3s ease;
        }
        
        .mockup-medication-modal.active {
            display: flex;
        }
        
        .mockup-medication-modal-content {
            background: var(--card-light);
            border-radius: 20px;
            padding: 2rem;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            animation: scaleInModal 0.3s ease;
        }
        
        @media (prefers-color-scheme: dark) {
            .mockup-medication-modal-content {
                background: var(--card-dark);
            }
        }
        
        body.dark .mockup-medication-modal-content {
            background: var(--card-dark);
        }
        
        .mockup-medication-modal-close {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(0, 0, 0, 0.1);
            border: none;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-light);
            font-size: 18px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .mockup-medication-modal-close:hover {
            background: rgba(0, 0, 0, 0.2);
            transform: scale(1.1);
        }
        
        @media (prefers-color-scheme: dark) {
            .mockup-medication-modal-close {
                color: var(--text-dark);
            }
        }
        
        body.dark .mockup-medication-modal-close {
            color: var(--text-dark);
        }
        
        .mockup-medication-modal-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--text-light);
            margin-bottom: 1rem;
        }
        
        @media (prefers-color-scheme: dark) {
            .mockup-medication-modal-title {
                color: var(--text-dark);
            }
        }
        
        body.dark .mockup-medication-modal-title {
            color: var(--text-dark);
        }
        
        .mockup-medication-modal-appointment {
            background: rgba(245, 158, 11, 0.1);
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 1rem;
        }
        
        .mockup-medication-modal-appointment-header {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-light);
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid rgba(245, 158, 11, 0.3);
        }
        
        @media (prefers-color-scheme: dark) {
            .mockup-medication-modal-appointment-header {
                color: var(--text-dark);
                border-bottom-color: rgba(245, 158, 11, 0.5);
            }
        }
        
        body.dark .mockup-medication-modal-appointment-header {
            color: var(--text-dark);
            border-bottom-color: rgba(245, 158, 11, 0.5);
        }
        
        .mockup-medication-modal-drug {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 0.75rem;
        }
        
        @media (prefers-color-scheme: dark) {
            .mockup-medication-modal-drug {
                background: rgba(255, 255, 255, 0.05);
            }
        }
        
        .mockup-medication-modal-drug-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-light);
            margin-bottom: 0.5rem;
        }
        
        @media (prefers-color-scheme: dark) {
            .mockup-medication-modal-drug-name {
                color: var(--text-dark);
            }
        }
        
        body.dark .mockup-medication-modal-drug-name {
            color: var(--text-dark);
        }
        
        .mockup-medication-modal-drug-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.5rem;
            font-size: 0.9rem;
            color: var(--subtitle-light);
        }
        
        @media (prefers-color-scheme: dark) {
            .mockup-medication-modal-drug-details {
                color: var(--subtitle-dark);
            }
        }
        
        body.dark .mockup-medication-modal-drug-details {
            color: var(--subtitle-dark);
        }
        
        .mockup-medication-modal-drug-detail {
            display: flex;
            gap: 0.5rem;
        }
        
        .mockup-medication-modal-drug-label {
            font-weight: 600;
            min-width: 80px;
        }
        
        .mockup-medication-modal-drug-value {
            opacity: 0.9;
        }
        
        /* Ajax Mockup */
        .mockup-ajax {
            padding: 12px;
            height: 100%;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .mockup-ajax-section {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            border-radius: 8px;
            padding: 10px;
            position: relative;
            overflow: hidden;
        }
        
        .mockup-ajax-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            animation: ajaxLoad 2s infinite;
        }
        
        .mockup-ajax-title {
            height: 12px;
            border-radius: 4px;
            width: 40%;
            margin-bottom: 20px !important;
            position: relative;
        }
        
        .mockup-ajax-content {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            min-height: 30px;
            margin-bottom: 20px !important;
        }
        
        .mockup-ajax-item {
            height: 24px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 4px;
            flex: 1;
            min-width: 60px;
            position: relative;
            overflow: hidden;
            margin-bottom: 20px !important;
        }
        
        .mockup-ajax-item.adding {
            animation: ajaxAdd 0.6s ease-out;
        }
        
        .mockup-ajax-item.editing {
            animation: ajaxEdit 0.5s ease-out;
        }
        
        .mockup-ajax-item.deleting {
            animation: ajaxDelete 0.5s ease-out forwards;
        }
        
        .mockup-ajax-item::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            animation: ajaxShine 2s infinite;
        }
        
        .mockup-ajax-item.deleting::after {
            animation: none;
        }
        
        .mockup-ajax-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 12px;
            opacity: 0.8;
        }
        
        /* Performance Charts */
        .performance-charts {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            padding: 1.5rem;
            min-height: 500px;
        }
        
        .performance-chart-container {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            padding: 1.25rem;
            position: relative;
            min-height: 200px;
            display: flex;
            flex-direction: column;
        }
        
        @media (prefers-color-scheme: dark) {
            .performance-chart-container {
                background: rgba(255, 255, 255, 0.03);
            }
        }
        
        body.dark .performance-chart-container {
            background: rgba(255, 255, 255, 0.03);
        }
        
        .performance-chart-title {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-light);
            margin-bottom: 0.25rem;
            text-align: center;
        }
        
        @media (prefers-color-scheme: dark) {
            .performance-chart-title {
                color: var(--text-dark);
            }
        }
        
        body.dark .performance-chart-title {
            color: var(--text-dark);
        }
        
        .performance-chart-hint {
            font-size: 0.75rem;
            font-weight: 500;
            text-align: center;
            margin-bottom: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            display: inline-block;
            width: 100%;
        }
        
        .performance-chart-hint.lower {
            color: #10b981;
            background: rgba(16, 185, 129, 0.1);
        }
        
        .performance-chart-hint.higher {
            color: #3b82f6;
            background: rgba(59, 130, 246, 0.1);
        }
        
        @media (prefers-color-scheme: dark) {
            .performance-chart-hint.lower {
                color: #34d399;
                background: rgba(52, 211, 153, 0.15);
            }
            
            .performance-chart-hint.higher {
                color: #60a5fa;
                background: rgba(96, 165, 250, 0.15);
            }
        }
        
        body.dark .performance-chart-hint.lower {
            color: #34d399;
            background: rgba(52, 211, 153, 0.15);
        }
        
        body.dark .performance-chart-hint.higher {
            color: #60a5fa;
            background: rgba(96, 165, 250, 0.15);
        }
        
        .performance-chart-canvas {
            flex: 1;
            min-height: 180px;
            max-height: 220px;
        }
        
        @media (max-width: 768px) {
            .performance-charts {
                grid-template-columns: 1fr;
                min-height: auto;
            }
            
            .feature-mockup-inner {
                min-height: auto;
            }
        }
        
        /* Animations */
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.8; transform: scale(0.98); }
        }
        
        @keyframes shine {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(200%); }
        }
        
        @keyframes slideIn {
            from { transform: translateX(-20px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes shimmer {
            0%, 100% { opacity: 0.3; }
            50% { opacity: 0.6; }
        }
        
        @keyframes expand {
            from { transform: scaleY(0); opacity: 0; }
            to { transform: scaleY(1); opacity: 1; }
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes ajaxLoad {
            0% { left: -100%; }
            100% { left: 100%; }
        }
        
        @keyframes ajaxAdd {
            from { 
                transform: scale(0) translateY(-10px); 
                opacity: 0; 
            }
            to { 
                transform: scale(1) translateY(0); 
                opacity: 1; 
            }
        }
        
        @keyframes ajaxEdit {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); background: rgba(255, 255, 255, 0.3); }
        }
        
        @keyframes ajaxDelete {
            to { 
                transform: scale(0) translateX(-20px); 
                opacity: 0; 
                height: 0;
                margin: 0;
                padding: 0;
            }
        }
        
        @keyframes ajaxShine {
            0% { left: -100%; }
            100% { left: 100%; }
        }
        
        
        .back-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50px;
            padding: 0.75rem 1.5rem;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            z-index: 1000;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .back-button:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.05);
            color: white;
        }
        
        @media (prefers-color-scheme: dark) {
            .back-button {
                background: rgba(0, 0, 0, 0.3);
                border-color: rgba(255, 255, 255, 0.2);
            }
            
            .back-button:hover {
                background: rgba(0, 0, 0, 0.5);
            }
        }
        
        body.dark .back-button {
            background: rgba(0, 0, 0, 0.3);
            border-color: rgba(255, 255, 255, 0.2);
        }
        
        body.dark .back-button:hover {
            background: rgba(0, 0, 0, 0.5);
        }
        
        .footer {
            text-align: center;
            margin-top: 4rem;
            padding: 2rem 0;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .clinic-version {
            color: var(--subtitle-light);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            text-align: center;
            direction: ltr;
        }
        
        .clinic-version span {
            direction: rtl;
            display: inline-block;
        }
        
        @media (prefers-color-scheme: dark) {
            .clinic-version {
                color: var(--subtitle-dark);
            }
        }
        
        body.dark .clinic-version {
            color: var(--subtitle-dark);
        }
        
        .clinic-author {
            color: var(--subtitle-light);
            font-size: 0.85rem;
            margin-bottom: 1rem;
            text-align: center;
            direction: ltr;
        }
        
        @media (prefers-color-scheme: dark) {
            .clinic-author {
                color: var(--subtitle-dark);
            }
        }
        
        body.dark .clinic-author {
            color: var(--subtitle-dark);
        }
        
        .whats-new-link {
            color: var(--accent-light) !important;
            font-weight: 500;
            text-decoration: underline !important;
            transition: color 0.2s ease;
        }
        
        .whats-new-link:hover {
            color: var(--accent-light) !important;
            opacity: 0.8;
        }
        
        @media (prefers-color-scheme: dark) {
            .whats-new-link {
                color: var(--accent-dark) !important;
            }
            
            .whats-new-link:hover {
                color: var(--accent-dark) !important;
            }
        }
        
        body.dark .whats-new-link {
            color: var(--accent-dark) !important;
        }
        
        body.dark .whats-new-link:hover {
            color: var(--accent-dark) !important;
        }
        
        .sidebar-footer-link {
            color: var(--accent-light) !important;
            transition: color 0.2s ease;
        }
        
        .sidebar-footer-link:hover {
            color: var(--accent-light) !important;
            opacity: 0.8;
        }
        
        @media (prefers-color-scheme: dark) {
            .sidebar-footer-link {
                color: var(--accent-dark) !important;
            }
            
            .sidebar-footer-link:hover {
                color: var(--accent-dark) !important;
            }
        }
        
        body.dark .sidebar-footer-link {
            color: var(--accent-dark) !important;
        }
        
        body.dark .sidebar-footer-link:hover {
            color: var(--accent-dark) !important;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .page-title {
                font-size: 2rem;
            }
            
            .page-subtitle {
                font-size: 1rem;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
            }
            
            .back-button {
                top: 10px;
                right: 10px;
                padding: 0.5rem 0.75rem;
                font-size: 1rem;
            }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .feature-card {
            animation: fadeInUp 0.6s ease both;
        }
        
        .feature-card:nth-child(1) { animation-delay: 0.1s; }
        .feature-card:nth-child(2) { animation-delay: 0.2s; }
        .feature-card:nth-child(3) { animation-delay: 0.3s; }
        .feature-card:nth-child(4) { animation-delay: 0.4s; }
        .feature-card:nth-child(5) { animation-delay: 0.5s; }
    </style>
</head>
<body>    <!-- Back Button -->
    <a href="/" class="back-button">
        <i class="bi bi-arrow-left"></i>
        Back to Login
    </a>
    
    <div class="container">
        <div class="header">
            <div class="logo">
                <img src="/assets/images/Dark.png" alt="Roaya Clinic Logo" id="clinicLogo" style="max-width: 120px; height: auto; transition: transform 0.3s ease;">
            </div>
            <div class="version-badge">Version 6.0</div>
            <h1 class="page-title">What's New</h1>
            <p class="page-subtitle">Discover the amazing new features and improvements</p>
            <div class="mt-3">
                <a href="/whats-new/older-versions" target="_blank" class="btn btn-outline-light btn-lg" style="text-decoration: none;">
                    <i class="bi bi-clock-history me-2"></i>
                    View Older Version Features
                </a>
            </div>
        </div>
        
        <div class="features-grid">
            <!-- Media Gallery -->
            <div class="feature-card" style="max-height: 950px;">
                <i class="bi bi-images feature-icon"></i>
                <h3 class="feature-title">Media Gallery</h3>
                <p class="feature-description">
                    A comprehensive media management system that organizes all patient images and attachments in one place.
                </p>
                <ul class="feature-list">
                    <li>Group images by patient</li>
                    <li>Thumbnail preview with image count</li>
                    <li>Full-screen carousel viewer</li>
                    <li>Patient name filtering with autocomplete</li>
                    <li>Load more pagination</li>
                    <li>Direct links to appointments and visits</li>
                </ul>

                <div class="mockup-badge">
                        <i class="bi bi-cursor me-2"></i>Click on any item to see how it works
                </div>
                <br>
                <div class="feature-mockup" style="max-height: 350px;">
                    <div class="feature-mockup-inner">
                        <div class="mockup-media">
                            <div class="mockup-media-item" data-image="1">
                                <img src="https://images.unsplash.com/photo-1559757148-5c350d0d3c56?w=200&h=200&fit=crop" alt="Patient 1" class="mockup-media-image">
                                <div class="mockup-media-overlay">
                                    <p class="mockup-media-patient-name">Ahmed Mohamed</p>
                                </div>
                                <div class="mockup-media-badge">12</div>
                            </div>
                            <div class="mockup-media-item" data-image="2">
                                <img src="https://images.unsplash.com/photo-1551836022-d5d88e9218df?w=200&h=200&fit=crop" alt="Patient 2" class="mockup-media-image">
                                <div class="mockup-media-overlay">
                                    <p class="mockup-media-patient-name">Sara Ali</p>
                                </div>
                                <div class="mockup-media-badge">8</div>
                            </div>
                            <div class="mockup-media-item" data-image="3">
                                <img src="https://images.unsplash.com/photo-1582750433449-648ed127bb54?w=200&h=200&fit=crop" alt="Patient 3" class="mockup-media-image">
                                <div class="mockup-media-overlay">
                                    <p class="mockup-media-patient-name">Mohamed Hassan</p>
                                </div>
                                <div class="mockup-media-badge">5</div>
                            </div>
                            <div class="mockup-media-item" data-image="4">
                                <img src="https://images.unsplash.com/photo-1551601651-2a8555f1a136?w=200&h=200&fit=crop" alt="Patient 4" class="mockup-media-image">
                                <div class="mockup-media-overlay">
                                    <p class="mockup-media-patient-name">Fatima Ibrahim</p>
                                </div>
                                <div class="mockup-media-badge">15</div>
                            </div>
                            <div class="mockup-media-item" data-image="5">
                                <img src="https://images.unsplash.com/photo-1559757175-0eb30cd8c063?w=200&h=200&fit=crop" alt="Patient 5" class="mockup-media-image">
                                <div class="mockup-media-overlay">
                                    <p class="mockup-media-patient-name">Omar Khaled</p>
                                </div>
                                <div class="mockup-media-badge">3</div>
                            </div>
                            <div class="mockup-media-item" data-image="6">
                                <img src="https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=200&h=200&fit=crop" alt="Patient 6" class="mockup-media-image">
                                <div class="mockup-media-overlay">
                                    <p class="mockup-media-patient-name">Layla Mahmoud</p>
                                </div>
                                <div class="mockup-media-badge">9</div>
                            </div>
                    </div>
                    </div>

                </div>
                
                <!-- Media Modal -->
                <div class="mockup-media-modal" id="mockupMediaModal">
                    <div class="mockup-media-modal-content">
                        <button class="mockup-media-modal-close" id="mockupMediaModalClose">
                            <i class="bi bi-x"></i>
                        </button>
                        <img src="" alt="Patient Image" class="mockup-media-modal-image" id="mockupMediaModalImage">
                    </div>
                </div>
            </div>
            
            <!-- Glasses Prescriptions -->
            <div class="feature-card" style="max-height: 950px;">
                <i class="bi bi-eyeglasses feature-icon"></i>
                <h3 class="feature-title">Glasses Prescriptions</h3>
                <p class="feature-description">
                    A dedicated gallery for managing and viewing all glasses prescriptions grouped by patient.
                </p>
                <ul class="feature-list">
                    <li>Patient-based organization</li>
                    <li>Prescription count display</li>
                    <li>Latest prescription preview</li>
                    <li>Print prescription functionality</li>
                    <li>View patient and appointment links</li>
                    <li>Full Dark Mode support</li>
                </ul>
                <div class="mockup-badge">
                        <i class="bi bi-cursor me-2"></i>Click on any item to see how it works
                    </div>
                <div class="feature-mockup" style="max-height: 350px;">
                    <div class="feature-mockup-inner">
                        <div class="mockup-glasses">
                        <div class="mockup-glasses-card" data-prescription='{"patient":"Ahmed Mohamed","sphereR":"-2.50","sphereL":"-2.00","cylinderR":"-0.75","cylinderL":"-1.00","axisR":"180","axisL":"90","pdR":"32","pdL":"32","lensType":"Progressive"}'>
                            <div class="mockup-glasses-icon">👓</div>
                            <div class="mockup-glasses-info">
                                <div class="mockup-glasses-name"></div>
                                <div class="mockup-glasses-prescription">
                                    <div class="mockup-glasses-prescription-row">
                                        <span class="mockup-glasses-prescription-label">Sphere:</span>
                                        <span class="mockup-glasses-prescription-value">R: -2.50 | L: -2.00</span>
                                    </div>
                                    <div class="mockup-glasses-prescription-row">
                                        <span class="mockup-glasses-prescription-label">Cylinder:</span>
                                        <span class="mockup-glasses-prescription-value">R: -0.75 | L: -1.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mockup-glasses-card" style="animation-delay: 0.2s;" data-prescription='{"patient":"Sara Ali","sphereR":"+1.50","sphereL":"+1.75","cylinderR":"-0.50","cylinderL":"-0.50","axisR":"90","axisL":"90","pdR":"30","pdL":"30","lensType":"Single Vision"}'>
                            <div class="mockup-glasses-icon">👓</div>
                            <div class="mockup-glasses-info">
                                <div class="mockup-glasses-name"></div>
                                <div class="mockup-glasses-prescription">
                                    <div class="mockup-glasses-prescription-row">
                                        <span class="mockup-glasses-prescription-label">Sphere:</span>
                                        <span class="mockup-glasses-prescription-value">R: +1.50 | L: +1.75</span>
                                    </div>
                                    <div class="mockup-glasses-prescription-row">
                                        <span class="mockup-glasses-prescription-label">Cylinder:</span>
                                        <span class="mockup-glasses-prescription-value">R: -0.50 | L: -0.50</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mockup-glasses-card" style="animation-delay: 0.4s;" data-prescription='{"patient":"Mohamed Hassan","sphereR":"-3.25","sphereL":"-3.00","cylinderR":"-1.25","cylinderL":"-1.50","axisR":"175","axisL":"5","pdR":"33","pdL":"33","lensType":"Bifocal"}'>
                            <div class="mockup-glasses-icon">👓</div>
                            <div class="mockup-glasses-info">
                                <div class="mockup-glasses-name"></div>
                                <div class="mockup-glasses-prescription">
                                    <div class="mockup-glasses-prescription-row">
                                        <span class="mockup-glasses-prescription-label">Sphere:</span>
                                        <span class="mockup-glasses-prescription-value">R: -3.25 | L: -3.00</span>
                                    </div>
                                    <div class="mockup-glasses-prescription-row">
                                        <span class="mockup-glasses-prescription-label">Cylinder:</span>
                                        <span class="mockup-glasses-prescription-value">R: -1.25 | L: -1.50</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mockup-glasses-card" style="animation-delay: 0.4s;" data-prescription='{"patient":"Ahmed Essam","sphereR":"-3.25","sphereL":"-3.00","cylinderR":"-1.25","cylinderL":"-1.50","axisR":"175","axisL":"5","pdR":"33","pdL":"33","lensType":"Bifocal"}'>
                            <div class="mockup-glasses-icon">👓</div>
                            <div class="mockup-glasses-info">
                                <div class="mockup-glasses-name"></div>
                                <div class="mockup-glasses-prescription">
                                    <div class="mockup-glasses-prescription-row">
                                        <span class="mockup-glasses-prescription-label">Sphere:</span>
                                        <span class="mockup-glasses-prescription-value">R: -3.25 | L: -3.00</span>
                                    </div>
                                    <div class="mockup-glasses-prescription-row">
                                        <span class="mockup-glasses-prescription-label">Cylinder:</span>
                                        <span class="mockup-glasses-prescription-value">R: -1.25 | L: -1.50</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
                
                <!-- Glasses Modal -->
                <div class="mockup-glasses-modal" id="mockupGlassesModal">
                    <div class="mockup-glasses-modal-content">
                        <button class="mockup-glasses-modal-close" id="mockupGlassesModalClose">
                            <i class="bi bi-x"></i>
                        </button>
                        <h3 class="mockup-glasses-modal-title" id="mockupGlassesModalTitle">Glasses Prescription</h3>
                        <div class="mockup-glasses-modal-prescription" id="mockupGlassesModalPrescription">
                            <!-- Prescription details will be inserted here -->
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Medication Prescriptions -->
            <div class="feature-card">
                <i class="bi bi-capsule feature-icon"></i>
                <h3 class="feature-title">Medication Prescriptions</h3>
                <p class="feature-description">
                    Organize and manage medication prescriptions grouped by patient and appointment.
                </p>
                <ul class="feature-list">
                    <li>Grouped by patient and appointment</li>
                    <li>Appointment count per patient</li>
                    <li>Accordion-style display</li>
                    <li>Latest appointment expanded by default</li>
                    <li>Print prescription for each appointment</li>
                    <li>Patient search with autocomplete</li>
                </ul>
                <div class="mockup-badge">
                        <i class="bi bi-cursor me-2"></i>Click on any item to see how it works
                    </div>
                <div class="feature-mockup" style="max-height: 460px;">
                    <div class="feature-mockup-inner">
                        <div class="mockup-medications">
                        <div class="mockup-medication-group" data-medication='{"patient":"Ahmed Mohamed","appointmentDate":"2025-01-15","appointmentTime":"10:00 AM","drugs":[{"name":"Paracetamol","dose":"500mg","frequency":"3 times daily","duration":"5 days","route":"Oral"},{"name":"Amoxicillin","dose":"250mg","frequency":"2 times daily","duration":"7 days","route":"Oral"}]}'>
                            <div class="mockup-medication-header">
                                <div class="mockup-medication-date"></div>
                            </div>
                            <div class="mockup-medication-items">
                                <div class="mockup-medication-item">
                                    <div class="mockup-medication-drug-name">Paracetamol</div>
                                    <div class="mockup-medication-drug-details">
                                        <span>500mg</span>
                                        <span>•</span>
                                        <span>3x daily</span>
                                        <span>•</span>
                                        <span>5 days</span>
                                    </div>
                                </div>
                                <div class="mockup-medication-item">
                                    <div class="mockup-medication-drug-name">Amoxicillin</div>
                                    <div class="mockup-medication-drug-details">
                                        <span>250mg</span>
                                        <span>•</span>
                                        <span>2x daily</span>
                                        <span>•</span>
                                        <span>7 days</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mockup-medication-group" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); opacity: 0.7;" data-medication='{"patient":"Sara Ali","appointmentDate":"2025-01-14","appointmentTime":"2:30 PM","drugs":[{"name":"Ibuprofen","dose":"400mg","frequency":"2 times daily","duration":"3 days","route":"Oral"},{"name":"Vitamin D","dose":"1000 IU","frequency":"Once daily","duration":"30 days","route":"Oral"}]}'>
                            <div class="mockup-medication-header">
                                <div class="mockup-medication-date"></div>
                            </div>
                            <div class="mockup-medication-items">
                                <div class="mockup-medication-item">
                                    <div class="mockup-medication-drug-name">Ibuprofen</div>
                                    <div class="mockup-medication-drug-details">
                                        <span>400mg</span>
                                        <span>•</span>
                                        <span>2x daily</span>
                                        <span>•</span>
                                        <span>3 days</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mockup-medication-group" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); opacity: 0.6; animation-delay: 0.3s;" data-medication='{"patient":"Mohamed Hassan","appointmentDate":"2025-01-13","appointmentTime":"11:00 AM","drugs":[{"name":"Aspirin","dose":"100mg","frequency":"Once daily","duration":"30 days","route":"Oral"},{"name":"Metformin","dose":"500mg","frequency":"2 times daily","duration":"90 days","route":"Oral"}]}'>
                            <div class="mockup-medication-header">
                                <div class="mockup-medication-date"></div>
                            </div>
                            <div class="mockup-medication-items">
                                <div class="mockup-medication-item">
                                    <div class="mockup-medication-drug-name">Aspirin</div>
                                    <div class="mockup-medication-drug-details">
                                        <span>100mg</span>
                                        <span>•</span>
                                        <span>1x daily</span>
                                        <span>•</span>
                                        <span>30 days</span>
                                    </div>
                                </div>
                                <div class="mockup-medication-item">
                                    <div class="mockup-medication-drug-name">Metformin</div>
                                    <div class="mockup-medication-drug-details">
                                        <span>500mg</span>
                                        <span>•</span>
                                        <span>2x daily</span>
                                        <span>•</span>
                                        <span>90 days</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mockup-medication-group" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); opacity: 0.5; animation-delay: 0.6s;" data-medication='{"patient":"Fatima Ibrahim","appointmentDate":"2025-01-12","appointmentTime":"3:45 PM","drugs":[{"name":"Amoxicillin","dose":"500mg","frequency":"3 times daily","duration":"7 days","route":"Oral"},{"name":"Paracetamol","dose":"500mg","frequency":"As needed","duration":"5 days","route":"Oral"}]}'>
                            <div class="mockup-medication-header">
                                <div class="mockup-medication-date"></div>
                            </div>
                            <div class="mockup-medication-items">
                                <div class="mockup-medication-item">
                                    <div class="mockup-medication-drug-name">Amoxicillin</div>
                                    <div class="mockup-medication-drug-details">
                                        <span>500mg</span>
                                        <span>•</span>
                                        <span>3x daily</span>
                                        <span>•</span>
                                        <span>7 days</span>
                                    </div>
                                </div>
                                <div class="mockup-medication-item">
                                    <div class="mockup-medication-drug-name">Paracetamol</div>
                                    <div class="mockup-medication-drug-details">
                                        <span>500mg</span>
                                        <span>•</span>
                                        <span>As needed</span>
                                        <span>•</span>
                                        <span>5 days</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
                
                <!-- Medication Modal -->
                <div class="mockup-medication-modal" id="mockupMedicationModal">
                    <div class="mockup-medication-modal-content">
                        <button class="mockup-medication-modal-close" id="mockupMedicationModalClose">
                            <i class="bi bi-x"></i>
                        </button>
                        <h3 class="mockup-medication-modal-title" id="mockupMedicationModalTitle">Medication Prescription</h3>
                        <div id="mockupMedicationModalContent">
                            <!-- Medication details will be inserted here -->
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Ajax Operations -->
            <div class="feature-card">
                <i class="bi bi-lightning-charge feature-icon"></i>
                <h3 class="feature-title">Ajax Operations</h3>
                <p class="feature-description">
                    Complete Ajax implementation for all operations without page reloads, providing a seamless and fast user experience.
                </p>
                <ul class="feature-list">
                    <li><strong>File Management:</strong> Upload and delete files instantly via Ajax</li>
                    <li><strong>Camera Capture:</strong> Direct camera integration for quick image capture</li>
                    <li><strong>Medications:</strong> Add, edit, and delete medication prescriptions dynamically</li>
                    <li><strong>Glasses Prescriptions:</strong> Full CRUD operations for glasses prescriptions</li>
                    <li><strong>Real-time Updates:</strong> Instant UI updates without page refresh</li>
                    <li><strong>Progress Indicators:</strong> Visual feedback for all operations</li>
                    <li><strong>Error Handling:</strong> Comprehensive validation and error messages</li>
                    <li><strong>Smooth Transitions:</strong> Elegant modal and form animations</li>
                </ul>
                <div class="feature-mockup">
                    <div class="mockup-ajax" id="mockupAjaxContainer">
                        <div class="mockup-ajax-section" id="ajaxSection1">
                            <div class="mockup-ajax-title">📁 Files</div>
                            <div class="mockup-ajax-content" id="ajaxContent1">
                                <!-- Items will be added dynamically -->
                            </div>
                        </div>
                        <div class="mockup-ajax-section" id="ajaxSection2" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);">
                            <div class="mockup-ajax-title">💊 Medications</div>
                            <div class="mockup-ajax-content" id="ajaxContent2">
                                <!-- Items will be added dynamically -->
                            </div>
                        </div>
                        <div class="mockup-ajax-section" id="ajaxSection3" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                            <div class="mockup-ajax-title">👓 Glasses</div>
                            <div class="mockup-ajax-content" id="ajaxContent3">
                                <!-- Items will be added dynamically -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Performance Improvements -->
            <div class="feature-card full-width">
                <i class="bi bi-speedometer2 feature-icon"></i>
                <h3 class="feature-title">Performance & Speed</h3>
                <p class="feature-description">
                    Significant performance improvements and optimizations throughout the system.
                </p>
                <ul class="feature-list">
                    <li>Faster page load times</li>
                    <li>Optimized database queries</li>
                    <li>Efficient pagination</li>
                    <li>Reduced server requests</li>
                    <li>Better caching strategies</li>
                    <li>Improved response times</li>
                </ul>
                <div class="feature-mockup">
                    <div class="feature-mockup-inner">
                        <div class="performance-charts">
                            <div class="performance-chart-container">
                                <div class="performance-chart-title">Page Load Times</div>
                                <div class="performance-chart-hint lower">Lower is better</div>
                                <canvas id="pageLoadChart" class="performance-chart-canvas"></canvas>
                            </div>
                            <div class="performance-chart-container">
                                <div class="performance-chart-title">Database Queries</div>
                                <div class="performance-chart-hint lower">Lower is better</div>
                                <canvas id="databaseChart" class="performance-chart-canvas"></canvas>
                            </div>
                            <div class="performance-chart-container">
                                <div class="performance-chart-title">Pagination Efficiency</div>
                                <div class="performance-chart-hint higher">Higher is better</div>
                                <canvas id="paginationChart" class="performance-chart-canvas"></canvas>
                            </div>
                            <div class="performance-chart-container">
                                <div class="performance-chart-title">Server Requests</div>
                                <div class="performance-chart-hint lower">Lower is better</div>
                                <canvas id="requestsChart" class="performance-chart-canvas"></canvas>
                            </div>
                            <div class="performance-chart-container">
                                <div class="performance-chart-title">Caching Strategy</div>
                                <div class="performance-chart-hint higher">Higher is better</div>
                                <canvas id="cachingChart" class="performance-chart-canvas"></canvas>
                            </div>
                            <div class="performance-chart-container">
                                <div class="performance-chart-title">Response Times</div>
                                <div class="performance-chart-hint lower">Lower is better</div>
                                <canvas id="responseChart" class="performance-chart-canvas"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="footer">
            <p class="clinic-version" dir="ltr" style="text-align: center;">
                <span dir="rtl">Version</span> v6.0 
                <a href="/whats-new" class="text-decoration-none whats-new-link" style="margin-right: 0.5rem; margin-left: 0.5rem;">What's New?</a>
            </p>
            <p class="clinic-author" dir="ltr" style="text-align: center;">
                HClinic / Roaya © 2025 <a href="https://ahmedhelal.dev" target="_blank" class="text-decoration-none sidebar-footer-link">Ahmed Helal</a>
            </p>
        </div>
    </div>
    
    <script>
        // Auto-detect dark mode from system preference
        const body = document.body;
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        
        if (prefersDark) {
            body.classList.add('dark');
        }
        
        // Update favicon and logo based on theme
        function updateFavicon() {
            const favicon = document.getElementById('favicon');
            if (body.classList.contains('dark')) {
                favicon.setAttribute('href', '/assets/images/Dark.png');
            } else {
                favicon.setAttribute('href', '/assets/images/Light.png');
            }
        }
        
        function updateLogo() {
            const logo = document.getElementById('clinicLogo');
            if (body.classList.contains('dark')) {
                logo.src = '/assets/images/Dark.png';
            } else {
                logo.src = '/assets/images/Light.png';
            }
        }
        
        // Initialize favicon and logo
        updateFavicon();
        updateLogo();
        
        // Listen for system theme changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (e.matches) {
                body.classList.add('dark');
            } else {
                body.classList.remove('dark');
            }
            updateFavicon();
            updateLogo();
        });
        
        // Add smooth page transition
        document.addEventListener('DOMContentLoaded', () => {
            document.body.style.opacity = '0';
            document.body.style.transition = 'opacity 0.3s ease';
            
            setTimeout(() => {
                document.body.style.opacity = '1';
            }, 100);
            
            // Media Gallery Mockup Modal
            setupMediaMockupModal();
            
            // Glasses Prescriptions Mockup Modal
            setupGlassesMockupModal();
            
            // Medication Prescriptions Mockup Modal
            setupMedicationMockupModal();
            
            // Ajax Operations Animation
            setupAjaxAnimation();
            
            // Performance Charts
            setupPerformanceCharts();
        });
        
        function setupMediaMockupModal() {
            const modal = document.getElementById('mockupMediaModal');
            const modalImage = document.getElementById('mockupMediaModalImage');
            const closeBtn = document.getElementById('mockupMediaModalClose');
            const mediaItems = document.querySelectorAll('.mockup-media-item');
            
            if (!modal || !modalImage || !closeBtn) return;
            
            // Open modal on click only
            mediaItems.forEach((item) => {
                item.addEventListener('click', function() {
                    const img = this.querySelector('.mockup-media-image');
                    if (img && img.src) {
                        modalImage.src = img.src;
                        modalImage.alt = img.alt;
                        modal.classList.add('active');
                        document.body.style.overflow = 'hidden';
                    }
                });
            });
            
            // Close modal
            closeBtn.addEventListener('click', function() {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            });
            
            // Close on background click
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
            
            // Close on Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modal.classList.contains('active')) {
                    modal.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
        }
        
        function setupGlassesMockupModal() {
            const modal = document.getElementById('mockupGlassesModal');
            const modalTitle = document.getElementById('mockupGlassesModalTitle');
            const modalPrescription = document.getElementById('mockupGlassesModalPrescription');
            const closeBtn = document.getElementById('mockupGlassesModalClose');
            const glassesCards = document.querySelectorAll('.mockup-glasses-card');
            
            if (!modal || !modalTitle || !modalPrescription || !closeBtn) return;
            
            // Open modal on click
            glassesCards.forEach((card) => {
                card.addEventListener('click', function() {
                    const prescriptionData = this.getAttribute('data-prescription');
                    if (prescriptionData) {
                        try {
                            const prescription = JSON.parse(prescriptionData);
                            
                            // Set title
                            modalTitle.textContent = `${prescription.patient} - Glasses Prescription`;
                            
                            // Build prescription HTML
                            modalPrescription.innerHTML = `
                                <div class="mockup-glasses-modal-prescription-row">
                                    <span class="mockup-glasses-modal-label">Patient:</span>
                                    <span class="mockup-glasses-modal-value">${prescription.patient}</span>
                                </div>
                                <div class="mockup-glasses-modal-prescription-row">
                                    <span class="mockup-glasses-modal-label">Lens Type:</span>
                                    <span class="mockup-glasses-modal-value">${prescription.lensType}</span>
                                </div>
                                <div class="mockup-glasses-modal-prescription-row">
                                    <span class="mockup-glasses-modal-label">Sphere (R):</span>
                                    <span class="mockup-glasses-modal-value">${prescription.sphereR} D</span>
                                </div>
                                <div class="mockup-glasses-modal-prescription-row">
                                    <span class="mockup-glasses-modal-label">Sphere (L):</span>
                                    <span class="mockup-glasses-modal-value">${prescription.sphereL} D</span>
                                </div>
                                <div class="mockup-glasses-modal-prescription-row">
                                    <span class="mockup-glasses-modal-label">Cylinder (R):</span>
                                    <span class="mockup-glasses-modal-value">${prescription.cylinderR} D</span>
                                </div>
                                <div class="mockup-glasses-modal-prescription-row">
                                    <span class="mockup-glasses-modal-label">Cylinder (L):</span>
                                    <span class="mockup-glasses-modal-value">${prescription.cylinderL} D</span>
                                </div>
                                <div class="mockup-glasses-modal-prescription-row">
                                    <span class="mockup-glasses-modal-label">Axis (R):</span>
                                    <span class="mockup-glasses-modal-value">${prescription.axisR}°</span>
                                </div>
                                <div class="mockup-glasses-modal-prescription-row">
                                    <span class="mockup-glasses-modal-label">Axis (L):</span>
                                    <span class="mockup-glasses-modal-value">${prescription.axisL}°</span>
                                </div>
                                <div class="mockup-glasses-modal-prescription-row">
                                    <span class="mockup-glasses-modal-label">PD (R):</span>
                                    <span class="mockup-glasses-modal-value">${prescription.pdR} mm</span>
                                </div>
                                <div class="mockup-glasses-modal-prescription-row">
                                    <span class="mockup-glasses-modal-label">PD (L):</span>
                                    <span class="mockup-glasses-modal-value">${prescription.pdL} mm</span>
                                </div>
                            `;
                            
                            modal.classList.add('active');
                            document.body.style.overflow = 'hidden';
                        } catch (e) {
                            console.error('Error parsing prescription data:', e);
                        }
                    }
                });
            });
            
            // Close modal
            closeBtn.addEventListener('click', function() {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            });
            
            // Close on background click
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
            
            // Close on Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modal.classList.contains('active')) {
                    modal.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
        }
        
        function setupMedicationMockupModal() {
            const modal = document.getElementById('mockupMedicationModal');
            const modalTitle = document.getElementById('mockupMedicationModalTitle');
            const modalContent = document.getElementById('mockupMedicationModalContent');
            const closeBtn = document.getElementById('mockupMedicationModalClose');
            const medicationGroups = document.querySelectorAll('.mockup-medication-group');
            
            if (!modal || !modalTitle || !modalContent || !closeBtn) return;
            
            // Open modal on click
            medicationGroups.forEach((group) => {
                group.addEventListener('click', function() {
                    const medicationData = this.getAttribute('data-medication');
                    if (medicationData) {
                        try {
                            const medication = JSON.parse(medicationData);
                            
                            // Set title
                            modalTitle.textContent = `${medication.patient} - Medication Prescription`;
                            
                            // Build medication HTML
                            let drugsHTML = '';
                            medication.drugs.forEach((drug, index) => {
                                drugsHTML += `
                                    <div class="mockup-medication-modal-drug">
                                        <div class="mockup-medication-modal-drug-name">${drug.name}</div>
                                        <div class="mockup-medication-modal-drug-details">
                                            <div class="mockup-medication-modal-drug-detail">
                                                <span class="mockup-medication-modal-drug-label">Dose:</span>
                                                <span class="mockup-medication-modal-drug-value">${drug.dose}</span>
                                            </div>
                                            <div class="mockup-medication-modal-drug-detail">
                                                <span class="mockup-medication-modal-drug-label">Frequency:</span>
                                                <span class="mockup-medication-modal-drug-value">${drug.frequency}</span>
                                            </div>
                                            <div class="mockup-medication-modal-drug-detail">
                                                <span class="mockup-medication-modal-drug-label">Duration:</span>
                                                <span class="mockup-medication-modal-drug-value">${drug.duration}</span>
                                            </div>
                                            <div class="mockup-medication-modal-drug-detail">
                                                <span class="mockup-medication-modal-drug-label">Route:</span>
                                                <span class="mockup-medication-modal-drug-value">${drug.route}</span>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            });
                            
                            modalContent.innerHTML = `
                                <div class="mockup-medication-modal-appointment">
                                    <div class="mockup-medication-modal-appointment-header">
                                        Appointment: ${medication.appointmentDate} at ${medication.appointmentTime}
                                    </div>
                                    ${drugsHTML}
                                </div>
                            `;
                            
                            modal.classList.add('active');
                            document.body.style.overflow = 'hidden';
                        } catch (e) {
                            console.error('Error parsing medication data:', e);
                        }
                    }
                });
            });
            
            // Close modal
            closeBtn.addEventListener('click', function() {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            });
            
            // Close on background click
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
            
            // Close on Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modal.classList.contains('active')) {
                    modal.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
        }
        
        function setupAjaxAnimation() {
            const section1 = document.getElementById('ajaxContent1');
            const section2 = document.getElementById('ajaxContent2');
            const section3 = document.getElementById('ajaxContent3');
            
            if (!section1 || !section2 || !section3) return;
            
            let currentStep = 0;
            let isAnimating = false;
            
            // Icons for different types
            const icons = {
                file: '📄',
                medication: '💊',
                glasses: '👓'
            };
            
            function createItem(icon, className = '') {
                const item = document.createElement('div');
                item.className = `mockup-ajax-item adding ${className}`;
                item.innerHTML = `<span class="mockup-ajax-icon">${icon}</span>`;
                return item;
            }
            
            function resetSections() {
                section1.innerHTML = '';
                section2.innerHTML = '';
                section3.innerHTML = '';
            }
            
            function animateStep() {
                if (isAnimating) return;
                isAnimating = true;
                
                switch(currentStep) {
                    case 0: // Add file
                        const file1 = createItem(icons.file);
                        section1.appendChild(file1);
                        setTimeout(() => file1.classList.remove('adding'), 600);
                        break;
                        
                    case 1: // Add another file
                        const file2 = createItem(icons.file);
                        section1.appendChild(file2);
                        setTimeout(() => file2.classList.remove('adding'), 600);
                        break;
                        
                    case 2: // Add medication
                        const med1 = createItem(icons.medication);
                        section2.appendChild(med1);
                        setTimeout(() => med1.classList.remove('adding'), 600);
                        break;
                        
                    case 3: // Add glasses
                        const glass1 = createItem(icons.glasses);
                        section3.appendChild(glass1);
                        setTimeout(() => glass1.classList.remove('adding'), 600);
                        break;
                        
                    case 4: // Edit file
                        const fileToEdit = section1.querySelector('.mockup-ajax-item');
                        if (fileToEdit) {
                            fileToEdit.classList.add('editing');
                            setTimeout(() => {
                                fileToEdit.classList.remove('editing');
                            }, 500);
                        }
                        break;
                        
                    case 5: // Add another medication
                        const med2 = createItem(icons.medication);
                        section2.appendChild(med2);
                        setTimeout(() => med2.classList.remove('adding'), 600);
                        break;
                        
                    case 6: // Delete file
                        const fileToDelete = section1.querySelector('.mockup-ajax-item');
                        if (fileToDelete) {
                            fileToDelete.classList.add('deleting');
                            setTimeout(() => {
                                fileToDelete.remove();
                            }, 500);
                        }
                        break;
                        
                    case 7: // Edit medication
                        const medToEdit = section2.querySelector('.mockup-ajax-item');
                        if (medToEdit) {
                            medToEdit.classList.add('editing');
                            setTimeout(() => {
                                medToEdit.classList.remove('editing');
                            }, 500);
                        }
                        break;
                        
                    case 8: // Add another glasses
                        const glass2 = createItem(icons.glasses);
                        section3.appendChild(glass2);
                        setTimeout(() => glass2.classList.remove('adding'), 600);
                        break;
                        
                    case 9: // Delete medication
                        const medToDelete = section2.querySelector('.mockup-ajax-item');
                        if (medToDelete) {
                            medToDelete.classList.add('deleting');
                            setTimeout(() => {
                                medToDelete.remove();
                            }, 500);
                        }
                        break;
                        
                    case 10: // Edit glasses
                        const glassToEdit = section3.querySelector('.mockup-ajax-item');
                        if (glassToEdit) {
                            glassToEdit.classList.add('editing');
                            setTimeout(() => {
                                glassToEdit.classList.remove('editing');
                            }, 500);
                        }
                        break;
                        
                    case 11: // Delete glasses
                        const glassToDelete = section3.querySelector('.mockup-ajax-item');
                        if (glassToDelete) {
                            glassToDelete.classList.add('deleting');
                            setTimeout(() => {
                                glassToDelete.remove();
                            }, 500);
                        }
                        break;
                }
                
                setTimeout(() => {
                    isAnimating = false;
                    currentStep++;
                    
                    if (currentStep > 11) {
                        // Reset and loop
                        currentStep = 0;
                        setTimeout(() => {
                            resetSections();
                            setTimeout(() => animateStep(), 500);
                        }, 1000);
                    } else {
                        animateStep();
                    }
                }, 1500);
            }
            
            // Start animation after page load
            setTimeout(() => {
                animateStep();
                }, 2000);
        }
        
        function setupPerformanceCharts() {
            const isDark = document.body.classList.contains('dark') || window.matchMedia('(prefers-color-scheme: dark)').matches;
            const textColor = isDark ? '#e5e7eb' : '#2c3e50';
            const gridColor = isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
            const beforeColor = isDark ? 'rgba(239, 68, 68, 0.8)' : 'rgba(239, 68, 68, 0.6)';
            const afterColor = isDark ? 'rgba(16, 185, 129, 0.8)' : 'rgba(16, 185, 129, 0.6)';
            
            const chartOptions = {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 1.2,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            color: textColor,
                            font: {
                                size: 11
                            },
                            padding: 8,
                            boxWidth: 12
                        }
                    },
                    tooltip: {
                        enabled: true,
                        backgroundColor: isDark ? 'rgba(0, 0, 0, 0.8)' : 'rgba(0, 0, 0, 0.7)',
                        titleColor: textColor,
                        bodyColor: textColor,
                        padding: 10
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: textColor,
                            font: {
                                size: 11
                            },
                            padding: 8
                        },
                        grid: {
                            color: gridColor,
                            lineWidth: 1
                        }
                    },
                    x: {
                        ticks: {
                            color: textColor,
                            font: {
                                size: 11
                            },
                            padding: 8
                        },
                        grid: {
                            color: gridColor,
                            lineWidth: 1
                        }
                    }
                }
            };
            
            // Page Load Times Chart
            const pageLoadCtx = document.getElementById('pageLoadChart');
            if (pageLoadCtx) {
                new Chart(pageLoadCtx, {
                    type: 'bar',
                    data: {
                        labels: ['Before', 'After'],
                        datasets: [{
                            label: 'Load Time (ms)',
                            data: [3200, 850],
                            backgroundColor: [beforeColor, afterColor],
                            borderRadius: 4
                        }]
                    },
                    options: chartOptions
                });
            }
            
            // Database Queries Chart
            const databaseCtx = document.getElementById('databaseChart');
            if (databaseCtx) {
                new Chart(databaseCtx, {
                    type: 'bar',
                    data: {
                        labels: ['Before', 'After'],
                        datasets: [{
                            label: 'Query Time (ms)',
                            data: [450, 120],
                            backgroundColor: [beforeColor, afterColor],
                            borderRadius: 4
                        }]
                    },
                    options: chartOptions
                });
            }
            
            // Pagination Efficiency Chart
            const paginationCtx = document.getElementById('paginationChart');
            if (paginationCtx) {
                new Chart(paginationCtx, {
                    type: 'bar',
                    data: {
                        labels: ['Before', 'After'],
                        datasets: [{
                            label: 'Items per Request',
                            data: [10, 50],
                            backgroundColor: [beforeColor, afterColor],
                            borderRadius: 4
                        }]
                    },
                    options: chartOptions
                });
            }
            
            // Server Requests Chart
            const requestsCtx = document.getElementById('requestsChart');
            if (requestsCtx) {
                new Chart(requestsCtx, {
                    type: 'bar',
                    data: {
                        labels: ['Before', 'After'],
                        datasets: [{
                            label: 'Requests per Page',
                            data: [15, 3],
                            backgroundColor: [beforeColor, afterColor],
                            borderRadius: 4
                        }]
                    },
                    options: chartOptions
                });
            }
            
            // Caching Strategy Chart
            const cachingCtx = document.getElementById('cachingChart');
            if (cachingCtx) {
                new Chart(cachingCtx, {
                    type: 'bar',
                    data: {
                        labels: ['Before', 'After'],
                        datasets: [{
                            label: 'Cache Hit Rate (%)',
                            data: [25, 85],
                            backgroundColor: [beforeColor, afterColor],
                            borderRadius: 4
                        }]
                    },
                    options: chartOptions
                });
            }
            
            // Response Times Chart
            const responseCtx = document.getElementById('responseChart');
            if (responseCtx) {
                new Chart(responseCtx, {
                    type: 'bar',
                    data: {
                        labels: ['Before', 'After'],
                        datasets: [{
                            label: 'Response Time (ms)',
                            data: [580, 95],
                            backgroundColor: [beforeColor, afterColor],
                            borderRadius: 4
                        }]
                    },
                    options: chartOptions
                });
            }
        }
    </script>
</body>
</html>

