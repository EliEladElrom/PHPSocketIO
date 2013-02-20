var io = require('socket.io').listen(8080);

io.sockets.on('connection', function (socket) {
    console.log('user connected');

    socket.on('join room', function (room) {
        socket.set('room', room, function() {
        console.log('room:: ' + room + ' saved'); } );
        socket.join(room);
    });

    socket.on('message', function(data) {
        console.log("Client data: " + data);
        socket.get('room', function(err, room) {
            io.sockets.in(room).emit('message', data);
        })
    });

    socket.on('php-message', function(data) {
        var dataArgs = JSON.parse(data);
        console.log('PHP:: ' + dataArgs.message);
        io.sockets.in(dataArgs.roomName).emit('message', dataArgs.message);
    });
});