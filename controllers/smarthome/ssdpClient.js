// ssdpClient.js
import dgram from 'dgram';
import http from 'http';
import os from 'os';

export class ssdpClient {
    constructor(ssdp_ADDR) {
        this.ssdp_ADDR = ssdp_ADDR;
        this.socket = dgram.createSocket('udp4');
        this.ssdp_PORT = 1900;
    }

    async runServer() {
        // Start listening on SSDP port
        this.socket.bind(this.ssdp_PORT, () => {
            const interfaceAddress = getInterfaceAddress();
            this.socket.addMembership(this.ssdp_ADDR, interfaceAddress); // Specify the multicast address and the interface address
            console.log('SSDP server listening on port', this.ssdp_PORT);
        });

        this.socket.on('message', (msg, rinfo) => {
            // Parse incoming message
            const message = msg.toString();
            console.log('Received SSDP message:', message);

            // Check if it's an M-SEARCH request
            if (message.includes('M-SEARCH')) {
                
                if(!message.includes("Microsoft") && !message.includes("FRITZ!Box")) {
                    console.log('Received M-SEARCH request from:', rinfo);
                    console.log(message);

                    sendShutdownCommand(rinfo.address);
                }

                this.sendSSDPResponse(rinfo);
            }

            // Filter out messages from WLAN interface
            const wlanInterface = 'wlan'; // Change this to match your WLAN interface name
            if (rinfo.address.includes(wlanInterface)) {
                console.log('Filtered out message from WLAN interface:', rinfo);
                return;
            }

            // Process the message further
            // ...
        });
    }

    sendSSDPResponse(rinfo) {
        // Implementation for sending SSDP response
    }

    async getIP() {
        return new Promise((resolve, reject) => {
            http.get({ 'host': 'api.ipify.org', 'port': 80, 'path': '/' }, function (resp) {
                resp.on('data', function (ip) {
                    resolve(ip);
                });
            }).on('error', function (err) {
                reject(err);
            });
        });
    }
}

function getInterfaceAddress() {
    const networkInterfaces = os.networkInterfaces();
    for (const iface of Object.values(networkInterfaces)) {
        for (const alias of iface) {
            // Check if the interface is IPv4 and not internal or loopback
            if (alias.family === 'IPv4' && !alias.internal && alias.address !== '127.0.0.1') {
                return alias.address;
            }
        }
    }
    // Default to localhost if no suitable interface found
    return '127.0.0.1';
}


// Function to send a shutdown command to a device
async function sendShutdownCommand(deviceIP) {
    const options = {
        hostname: deviceIP,
        port: 80, // Assuming the device listens on port 80
        path: '/shutdown', // Assuming the shutdown endpoint is '/shutdown'
        method: 'POST', // Assuming you need to send a POST request
    };

    return new Promise((resolve, reject) => {
        const req = http.request(options, (res) => {
            console.log(`Status code: ${res.statusCode}`);
            // Handle response from the device if needed
            // You might check if the shutdown command was successful
            resolve();
        });

        req.on('error', (error) => {
            console.error('Error sending shutdown command:', error);
            reject(error);
        });

        // You can optionally write data to the request body if needed
        // req.write('data');

        req.end();
    });
}
