const express = require('express');
const morgan = require('morgan');
const path = require('path');
const exphbs = require('express-handlebars');
const session = require('express-session');

const app = express();

app.set('views', path.join(__dirname, 'views'));
const hbs = exphbs.create({
    defaultLayout: 'main',
    extname: '.hbs',
    // ensure partials are loaded from views/partials
    partialsDir: path.join(__dirname, 'views', 'partials'),
    helpers: {
        eq: function (a, b) { return a === b; }
    }
});
app.engine('.hbs', hbs.engine);
app.set('view engine', '.hbs');

app.use(morgan('dev'));
app.use(express.json());
app.use(express.urlencoded({ extended: false }));
app.use(session({
    secret: 'mysecretkey', // Change this to a random secret key
    resave: false,
    saveUninitialized: false
}));

// Middleware: prevent caching of pages for authenticated users so Back button won't reveal them
app.use((req, res, next) => {
    try {
        if (req.session && req.session.user) {
            res.set('Cache-Control', 'no-store, no-cache, must-revalidate, proxy-revalidate');
            res.set('Pragma', 'no-cache');
            res.set('Expires', '0');
            res.set('Surrogate-Control', 'no-store');
        }
    } catch (e) {
        // Non-fatal, continue request pipeline and log for debugging
        console.error('Error setting cache headers:', e && e.message);
    }
    next();
});

//Rutas
app.use(require('./routes/index'));

app.use(express.static(path.join(__dirname, 'public')));
app.use('/images', express.static(path.join(__dirname, 'public/images')));

module.exports = app;