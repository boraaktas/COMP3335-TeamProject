from flask import Flask, request

app = Flask(__name__)
port = 3200

# Array to store alerts
alerts = []

@app.route('/')
def home():
    return 'Webhook server is running'

@app.route('/grafana-alert', methods=['POST'])
def grafana_alert():
    alert = request.json
    print('Received alert:', alert)

    # Store the alert in the alerts array
    alerts.append(alert)

    return 'Alert received', 200

@app.route('/alerts', methods=['GET'])
def get_alerts():
    alert_count = len(alerts)
    alert_details = f"""
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Alert Dashboard</title>
            <style>
                body {{
                    font-family: 'Arial', sans-serif;
                    margin: 0;
                    padding: 0;
                    background-color: #f8f9fa;
                    color: #333;
                }}
                header {{
                    background-color: #d90429;
                    color: white;
                    padding: 20px;
                    text-align: center;
                }}
                .container {{
                    max-width: 800px;
                    margin: 30px auto;
                    background: white;
                    border-radius: 8px;
                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                    padding: 20px;
                }}
                h1 {{
                    font-size: 24px;
                    color: white;
                }}
                .alert-count {{
                    font-size: 18px;
                    color: #d90429;
                    margin-bottom: 20px;
                }}
                .alert-card {{
                    border: 1px solid #d90429;
                    background: #ffe3e3;
                    padding: 15px;
                    margin-bottom: 15px;
                    border-radius: 5px;
                }}
                .alert-card p {{
                    margin: 0;
                    font-weight: bold;
                    color: #d90429;
                }}
                .no-alerts {{
                    text-align: center;
                    font-size: 18px;
                    color: #6c757d;
                }}
                footer {{
                    text-align: center;
                    padding: 10px;
                    font-size: 14px;
                    background-color: #f8f9fa;
                    color: #6c757d;
                    border-top: 1px solid #dee2e6;
                }}
            </style>
        </head>
        <body>
            <header>
                <h1>Alert Dashboard</h1>
            </header>
            <div class="container">
                <p class="alert-count"><strong>Total Alerts:</strong> {alert_count}</p>
    """

    if alert_count > 0:
        for index, alert in enumerate(alerts):
            # Extract the alert name from the message body if available
            message_body = alert.get('message', '')  # Assume message body is in the 'message' key
            alert_name = "Unnamed Alert"
            
            # Attempt to extract "alertname" from the message body
            if "alertname =" in message_body:
                try:
                    alert_name = message_body.split("alertname =")[1].split("-")[0].strip()
                except IndexError:
                    pass  # Keep default name if parsing fails

            alert_details += f"""
                <div class="alert-card">
                    <p>Alert {index + 1}: {alert_name}</p>
                </div>
            """
    else:
        alert_details += "<p class='no-alerts'>No alerts received yet.</p>"

    alert_details += """
            </div>
        </body>
        </html>
    """
    return alert_details

@app.route('/grafana-alert', methods=['GET'])
def grafana_alert_get():
    return 'GET request to /grafana-alert is not supported. This endpoint accepts POST requests only.', 405

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=port)