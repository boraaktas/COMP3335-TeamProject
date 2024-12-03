<?php
session_start();

// Initialize alerts array in session
if (!isset($_SESSION['alerts'])) {
    $_SESSION['alerts'] = [];
}

// Handle POST request to store alerts
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $alert = json_decode($input, true);

    if ($alert) {
        $_SESSION['alerts'][] = $alert;
        http_response_code(200);
        echo "Alert received";
        exit;
    } else {
        http_response_code(400);
        echo "Invalid alert format";
        exit;
    }
}

// Display alerts
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $alerts = $_SESSION['alerts'];
    $alertCount = count($alerts);

    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Grafana Alerts</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 0;
                background-color: #f4f4f9;
                color: #333;
            }
            header {
                background-color: #007bff;
                color: white;
                padding: 20px;
                text-align: center;
            }
            .container {
                max-width: 800px;
                margin: 20px auto;
                padding: 20px;
                background: white;
                border-radius: 8px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            }
            h1 {
                color: #007bff;
            }
            ul {
                list-style-type: none;
                padding: 0;
            }
            li {
                margin-bottom: 15px;
                border-bottom: 1px solid #ddd;
                padding-bottom: 10px;
            }
            strong {
                color: #007bff;
            }
            .alert-card {
                background: #f9f9fb;
                border: 1px solid #ddd;
                padding: 15px;
                margin-bottom: 10px;
                border-radius: 8px;
            }
            .alert-card ul {
                padding-left: 20px;
                list-style: square;
            }
            a {
                color: #007bff;
                text-decoration: none;
            }
            a:hover {
                text-decoration: underline;
            }
            .no-alerts {
                text-align: center;
                color: #666;
                font-size: 18px;
            }
            footer {
                text-align: center;
                padding: 10px;
                margin-top: 20px;
                font-size: 14px;
                background-color: #f1f1f1;
            }
        </style>
    </head>
    <body>
        <header>
            <h1>Grafana Alerts</h1>
        </header>
        <div class='container'>
            <p><strong>Total Alerts:</strong> $alertCount</p>";

    if ($alertCount > 0) {
        foreach ($alerts as $index => $alert) {
            $labels = isset($alert['labels']) ? $alert['labels'] : [];
            $annotations = isset($alert['annotations']) ? $alert['annotations'] : [];
            
            $labelDetails = "";
            foreach ($labels as $key => $value) {
                $labelDetails .= "<li>$key: $value</li>";
            }

            $annotationDetails = "";
            foreach ($annotations as $key => $value) {
                $annotationDetails .= "<li>$key: $value</li>";
            }

            $status = isset($alert['status']) ? $alert['status'] : 'Unknown';
            $message = isset($alert['message']) ? $alert['message'] : 'No message provided';
            $value = isset($alert['value']) ? $alert['value'] : 'N/A';
            $generatorURL = isset($alert['generatorURL']) ? $alert['generatorURL'] : '#';

            echo "<div class='alert-card'>
                    <p><strong>Alert " . ($index + 1) . "</strong></p>
                    <ul>
                        <li><strong>Status:</strong> $status</li>
                        <li><strong>Message:</strong> $message</li>
                        <li><strong>Value:</strong> $value</li>
                        <li><strong>Labels:</strong>
                            <ul>$labelDetails</ul>
                        </li>
                        <li><strong>Annotations:</strong>
                            <ul>$annotationDetails</ul>
                        </li>
                        <li><strong>Alert URL:</strong> <a href='$generatorURL' target='_blank'>View Alert</a></li>
                    </ul>
                  </div>";
        }
    } else {
        echo "<p class='no-alerts'>No alerts received yet.</p>";
    }

    echo "</div>
    </body>
    </html>";
    exit;
}

// For unsupported methods
http_response_code(405);
echo "Method Not Allowed. Only GET and POST requests are supported.";
exit;