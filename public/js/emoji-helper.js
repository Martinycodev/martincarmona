/**
 * Helper de iconos emoji para JS (Noto Color Emoji de Google).
 * Devuelve un string <img> para usar en innerHTML/template literals.
 *
 * Uso: emojiSvg('olive')
 *      emojiSvg('worker', '1.2rem')
 */
window.emojiSvg = function(name, size) {
    size = size || '1em';
    var map = {
        'olive':'emoji_u1fad2','tree':'emoji_u1f333','books':'emoji_u1f4da',
        'clipboard':'emoji_u1f4cb','euro':'emoji_u1f4b6','chart':'emoji_u1f4ca',
        'link':'emoji_u1f517','person':'emoji_u1f464','people':'emoji_u1f465',
        'bell':'emoji_u1f514','door':'emoji_u1f6aa','worker':'emoji_u1f477',
        'lock':'emoji_u1f512','pin':'emoji_u1f4cc','trash':'emoji_u1f5d1',
        'car':'emoji_u1f697','moneybag':'emoji_u1f4b0','testtube':'emoji_u1f9ea'
    };
    var file = map[name];
    if (!file) return name;
    var base = window._APP_BASE_PATH || '';
    return '<img src="' + base + '/public/img/emoji/' + file + '.svg"'
         + ' alt="' + name + '" class="emoji-icon"'
         + ' style="width:' + size + ';height:' + size + ';"'
         + ' draggable="false">';
};
