    // ╔══════════════════════════════════════════════════════════════════╗
    // ║  RILEVAMENTO BANDA DEBOLE                                        ║
    // ║  banda-check.js gira PRIMA di qualsiasi altro script/CSS.        ║
    // ║  Se la connessione è troppo lenta → redirect a /emergency.php    ║
    // ║  Il controllo avviene solo sulla Home Page per evitare loop.     ║
    // ╚══════════════════════════════════════════════════════════════════╝
window.BANDA_CHECK = {
    url:         '/emergency.php',
    sogliaMsImg: 3000,
    imgTest:     '/assets/img/logo.png',
    debug:       false,
};