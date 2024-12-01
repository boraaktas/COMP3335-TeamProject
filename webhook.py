from flask import Flask, request, jsonify

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
        <h1>Alert Details</h1>
        <p><strong>Total Alerts:</strong> {alert_count}</p>
    """

    if alert_count > 0:
        alert_details += '<ul>'
        for index, alert in enumerate(alerts):
            labels = ''.join([f"<li>{key}: {value}</li>" for key, value in alert.get('labels', {}).items()])
            annotations = ''.join([f"<li>{key}: {value}</li>" for key, value in alert.get('annotations', {}).items()])
            alert_details += f"""
                <li>
                    <strong>Alert {index + 1}:</strong>
                    <ul>
                        <li><strong>Status:</strong> {alert.get('status', 'Unknown')}</li>
                        <li><strong>Message:</strong> {alert.get('message', 'No message provided')}</li>
                        <li><strong>Value:</strong> {alert.get('value', 'N/A')}</li>
                        <li><strong>Labels:</strong>
                            <ul>
                                {labels}
                            </ul>
                        </li>
                        <li><strong>Annotations:</strong>
                            <ul>
                                {annotations}
                            </ul>
                        </li>
                        <li><strong>Alert URL:</strong> <a href="{alert.get('generatorURL', '#')}" target="_blank">View Alert</a></li>
                    </ul>
                </li>
            """
        alert_details += '</ul>'
    else:
        alert_details += '<p>No alerts received yet.</p>'

    return alert_details

@app.route('/grafana-alert', methods=['GET'])
def grafana_alert_get():
    return 'GET request to /grafana-alert is not supported. This endpoint accepts POST requests only.', 405

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=port)