const express = require('express');
const morgan = require('morgan');
const path = require('path');
const exphbs = require('express-handlebars');
const session = require('express-session');
const Handlebars = require('handlebars'); // Added Handlebars
const multer = require('multer'); // Added multer

const app = express();

app.set('views', path.join(__dirname, 'views'));
app.engine('.hbs', exphbs.create({
    defaultLayout: 'dashboard', // Ahora dashboard.hbs es el layout principal
    extname: '.hbs',
    layoutsDir: app.get('views'), // Busca layouts directamente en la carpeta de vistas
    helpers: { // Registered Handlebars helpers
        ifEquals: function(arg1, arg2, options) {
            if (!options || !options.fn || !options.inverse) {
                console.error("'ifEquals' helper called without a block. Make sure to use {{#ifEquals ...}}...{{/ifEquals}}");
                return ''; 
            }
            const val1 = String(arg1 || '').trim().toLowerCase();
            const val2 = String(arg2 || '').trim().toLowerCase();
            return (val1 === val2) ? options.fn(this) : options.inverse(this);
        }
    }
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