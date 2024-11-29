const express = require('express');
const bodyParser = require('body-parser');

const app = express();
const port = 3200;

// Array to store alerts
let alerts = [];

// Middleware to parse JSON data
app.use(bodyParser.json());

// Define a GET route for the root URL
app.get('/', (req, res) => {
    res.send('Webhook server is running');
});

// Define a POST endpoint for the webhook
app.post('/grafana-alert', (req, res) => {
    console.log('Received alert:', req.body);

    // Store the alert in the alerts array
    alerts.push(req.body);

    res.status(200).send('Alert received');
});

// Display the number of alerts and details on a webpage
app.get('/alerts', (req, res) => {
    const alertCount = alerts.length;
    let alertDetails = `
        <h1>Alert Details</h1>
        <p><strong>Total Alerts:</strong> ${alertCount}</p>
    `;

    if (alertCount > 0) {
        alertDetails += '<ul>';
        alerts.forEach((alert, index) => {
            alertDetails += `
                <li>
                    <strong>Alert ${index + 1}:</strong>
                    <ul>
                        <li><strong>Status:</strong> ${alert.status || 'Unknown'}</li>
                        <li><strong>Message:</strong> ${alert.message || 'No message provided'}</li>
                        <li><strong>Value:</strong> ${alert.value || 'N/A'}</li>
                        <li><strong>Labels:</strong>
                            <ul>
                                ${Object.entries(alert.labels || {}).map(([key, value]) => `<li>${key}: ${value}</li>`).join('')}
                            </ul>
                        </li>
                        <li><strong>Annotations:</strong>
                            <ul>
                                ${Object.entries(alert.annotations || {}).map(([key, value]) => `<li>${key}: ${value}</li>`).join('')}
                            </ul>
                        </li>
                        <li><strong>Alert URL:</strong> <a href="${alert.generatorURL || '#'}" target="_blank">View Alert</a></li>
                    </ul>
                </li>
            `;
        });
        alertDetails += '</ul>';
    } else {
        alertDetails += '<p>No alerts received yet.</p>';
    }

    res.send(alertDetails);
});

// Add a GET route for /grafana-alert
app.get('/grafana-alert', (req, res) => {
    res.send('GET request to /grafana-alert is not supported. This endpoint accepts POST requests only.');
});

// Start the server
app.listen(port, '0.0.0.0', () => {
    console.log(`Webhook server is running at http://localhost:${port}`);
});
