var dotenv = require("dotenv");
dotenv.config();
var express = require('express');
var app = express();
var MongoClient = require('mongodb').MongoClient;
var ObjectId = require('mongodb').ObjectId;
var mongoUrl = process.env.MONGO_DB_URL;
var mongoDB = process.env.MONGO_DB_DATABASE;
var server = require('http').createServer(app);
var io = require('socket.io')(server, {
    cors: {
        origin: "http://localhost",
        methods: ["GET", "POST"]
    }
});

var connections = {};

io.on('connection', (socket) => {
    connections[socket.handshake.query.userid] = socket.id;
    socket.userid = socket.handshake.query.userid;
    console.log('New client connection: ' + '{userid: ' + socket.userid + ', socketid: ' + connections[socket.handshake.query.userid] + '}');
    io.emit('set-user-status', JSON.stringify({
        "_id" : socket.userid,
        "online" : true,
        "last_connection_date" : ""
    }));

    socket.on('message', (message) => {
        console.log('Message: ' + message);
        var json = JSON.parse(message);
        var user_to = json.to;
        io.to(connections[user_to]).emit('private-message', message);
    });

    socket.on('disconnect', () => {
        console.log('Client disconnected: ' + socket.userid);
        delete connections[socket.userid];
        var currentdate = new Date(); 
        var datetime = currentdate.getFullYear() + "-" 
            + (((currentdate.getMonth() + 1) < 10) ? "0" + (currentdate.getMonth() + 1) : (currentdate.getMonth() + 1))  + "-" 
            + ((currentdate.getDate() < 10) ? "0" + currentdate.getDate() : currentdate.getDate())  + " "  
            + ((currentdate.getHours() < 10) ? "0" + currentdate.getHours() : currentdate.getHours()) + ":"  
            + ((currentdate.getMinutes() < 10) ? "0" + currentdate.getMinutes() : currentdate.getMinutes()) + ":" 
            + ((currentdate.getSeconds() < 10) ? "0" + currentdate.getSeconds() : currentdate.getSeconds());
        MongoClient.connect(mongoUrl, function(err, db) {
            if (err) console.log(err);
            var dbo = db.db(mongoDB);
            var query = { _id: ObjectId(socket.userid) };
            var newLastConnDate = { $set: {last_time_connected: datetime } };
            dbo.collection("users").updateOne(query, newLastConnDate, function(err, res) {
                if (err) console.log(err);
                console.log('Last connection date updated successfully');
                io.emit('set-user-status', JSON.stringify({
                    "_id" : socket.userid,
                    "online" : false,
                    "last_connection_date" : datetime
                }));
                db.close();
            });
        });
    })

    socket.on('get-user-status', (userid) => {
        var datetime = "";
        var online = false;
        if(connections[userid]) online = true;
        MongoClient.connect(mongoUrl, function(err, db) {
            if (err) console.log(err);
            var dbo = db.db(mongoDB);
            var query = { _id: ObjectId(userid) };
            dbo.collection("users").findOne(query, function(err, res) {
                if (err) console.log(err);
                datetime = res.last_time_connected;
                db.close();
                io.emit('set-user-status', JSON.stringify({
                    "_id" : userid,
                    "online" : online,
                    "last_connection_date" : (datetime != undefined) ? datetime : "Desconocido"
                }));
            });
        });
    });

    socket.on('typing-to-private', (userid) => {
        io.to(connections[userid]).emit('received-typing-private', socket.userid);
    });

    socket.on('notify-pending-messages-viewed', (data) => {
        var json = JSON.parse(data);
        var user_to = json.to;
        io.to(connections[user_to]).emit('pending-messages-viewed', json.chat_id);
    });
});

server.listen(3000, () => {
    console.log('Server running on port 3000')
})