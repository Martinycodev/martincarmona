<?php
/**
 * Helper de iconos emoji (Noto Color Emoji de Google).
 * Devuelve un <img> apuntando al SVG local para que se vea igual
 * en todos los navegadores y sistemas operativos.
 *
 * Uso en vistas:  <?= emoji('olive') ?>
 * Con tamaño:     <?= emoji('tree', '1.4rem') ?>
 */
function emoji(string $name, string $size = '1em', string $alt = ''): string
{
    // Mapa de nombres amigables → fichero SVG (Noto usa codepoints Unicode)
    static $map = [
        'olive'       => 'emoji_u1fad2',    // 🫒
        'tree'        => 'emoji_u1f333',    // 🌳
        'books'       => 'emoji_u1f4da',    // 📚
        'clipboard'   => 'emoji_u1f4cb',    // 📋
        'euro'        => 'emoji_u1f4b6',    // 💶
        'chart'       => 'emoji_u1f4ca',    // 📊
        'link'        => 'emoji_u1f517',    // 🔗
        'person'      => 'emoji_u1f464',    // 👤
        'people'      => 'emoji_u1f465',    // 👥
        'bell'        => 'emoji_u1f514',    // 🔔
        'door'        => 'emoji_u1f6aa',    // 🚪
        'worker'      => 'emoji_u1f477',    // 👷
        'lock'        => 'emoji_u1f512',    // 🔒
        'pin'         => 'emoji_u1f4cc',    // 📌
        'trash'       => 'emoji_u1f5d1',    // 🗑
        'car'         => 'emoji_u1f697',    // 🚗
        'moneybag'    => 'emoji_u1f4b0',    // 💰
        'testtube'    => 'emoji_u1f9ea',    // 🧪
    ];

    $file = $map[$name] ?? null;
    if (!$file) {
        return $name; // Fallback: devuelve el texto tal cual
    }

    $basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
    $src      = $basePath . '/public/img/emoji/' . $file . '.svg';
    $altText  = $alt ?: $name;

    return '<img src="' . $src . '"'
         . ' alt="' . htmlspecialchars($altText) . '"'
         . ' class="emoji-icon"'
         . ' style="width:' . $size . ';height:' . $size . ';"'
         . ' loading="lazy" draggable="false">';
}
