const { Client } = require('whatsapp-web.js');
const axios = require('axios');
const qrcode = require('qrcode-terminal');

const client = new Client();

client.on('qr', (qr) => {
    console.log('QR RECEIVED');
    qrcode.generate(qr, { small: true });
});

client.on('ready', () => {
    console.log('WhatsApp Client is ready!');
});

client.on('message', async (msg) => {
    if (msg.body.startsWith('!ticket ')) {
        const description = msg.body.slice(8); // Ambil deskripsi setelah "!ticket "
        try {
            // Kirim data tiket ke API Laravel
            const response = await axios.post('http://192.168.95.130:8001/api/create/ticket', {
                no_user: msg.from,
                deskripsi: description,
                status: 'Menunggu'
            });
            await msg.reply('Tiket Anda telah dibuat dengan ID: ' + response.data.ticket_id);
        } catch (error) {
            console.error('Error creating ticket:', error);
            await msg.reply('Gagal membuat tiket. Silakan coba lagi.');
        }
    }
});

// Fungsi untuk mengirim notifikasi ke pengguna
async function sendNotification(phone, message) {
    try {
        await client.sendMessage(phone, message);
        console.log(`Notifikasi dikirim ke ${phone}: ${message}`);
    } catch (error) {
        console.error('Error sending notification:', error);
    }
}

// Endpoint untuk menerima update status dari Laravel
const express = require('express');
const app = express();
app.use(express.json());

app.post('/notify', async (req, res) => {
    const { phone, status, ticket_id, ts, resolution_notes } = req.body;
    let message = '';
    if (status === 'Di Proses') {
        message = `Tiket Anda (#${ticket_id}) sedang dikerjakan oleh tim kami | (#${ts}).`;
    } else if (status === 'Selesai') {
        message = `Tiket Anda (#${ticket_id}) telah selesai. Catatan: ${resolution_notes || 'Tidak ada catatan.'}`;
    } else if (status === 'Terkendala') {
        message = `Tiket Anda (#${ticket_id}) terkendala: ${resolution_notes || 'Silakan hubungi support.'}`;
    }
    await sendNotification(phone, message);
    res.status(200).send('Notifikasi dikirim');
});

app.listen(3000, () => console.log('Notification server running on port 3000'));

client.initialize();