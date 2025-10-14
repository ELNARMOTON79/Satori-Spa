const express = require('express');
const morgan = require('morgan');
const path = require('path');
const exphbs = require('express-handlebars');
const session = require('express-session');

const app = express();

app.set('views', path.join(__dirname, 'views'));
app.engine('.hbs', exphbs.create({
    defaultLayout: 'main',
    extname: '.hbs',
}).engine);
app.set('view engine', '.hbs');

app.use(morgan('dev'));
app.use(express.json());
app.use(express.urlencoded({ extended: false }));
app.use(session({
    secret: 'mysecretkey', // Change this to a random secret key
    resave: false,
    saveUninitialized: false
}));

//Rutas
app.use(require('./routes/index'));

app.use(express.static(path.join(__dirname, 'public')));
app.use('/images', express.static(path.join(__dirname, 'public/images')));

module.exports = app;