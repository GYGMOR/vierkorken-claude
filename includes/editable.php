<?php
// includes/editable.php
// Helper-Funktion für editierbare Inhalte

/**
 * Gibt einen editierbaren Text aus
 * @param string $key - Eindeutige Datenbank-Key
 * @param string $content - Der anzuzeigende Text
 * @param string $tag - HTML-Tag (p, h1, h2, h3, span, div)
 * @param array $class - CSS-Klassen
 */
function editable($key, $content, $tag = 'p', $class = []) {
    global $EDIT_MODE;
    
    $class_str = implode(' ', $class);
    $edit_attr = '';
    $edit_class = '';
    
    if ($EDIT_MODE) {
        $edit_class = ' editable';
        $edit_attr = ' data-edit-key="' . htmlspecialchars($key) . '" data-type="text" style="cursor: pointer;"';
    }
    
    $safe_content = htmlspecialchars($content);
    
    echo "<$tag class='$class_str$edit_class'$edit_attr>$safe_content</$tag>";
}

/**
 * Gibt einen editierbaren Textarea-Inhalt aus
 */
function editable_textarea($key, $content, $tag = 'div', $class = []) {
    global $EDIT_MODE;
    
    $class_str = implode(' ', $class);
    $edit_attr = '';
    $edit_class = '';
    
    if ($EDIT_MODE) {
        $edit_class = ' editable';
        $edit_attr = ' data-edit-key="' . htmlspecialchars($key) . '" data-type="textarea" style="cursor: pointer;"';
    }
    
    $safe_content = htmlspecialchars($content);
    
    echo "<$tag class='$class_str$edit_class'$edit_attr>$safe_content</$tag>";
}

/**
 * Gibt einen editierbaren Link aus
 */
function editable_link($key, $url, $text, $class = []) {
    global $EDIT_MODE;
    
    $class_str = implode(' ', $class);
    $edit_attr = '';
    $edit_class = '';
    
    if ($EDIT_MODE) {
        $edit_class = ' editable';
        $edit_attr = ' data-edit-key="' . htmlspecialchars($key) . '" data-type="url" style="cursor: pointer;"';
    }
    
    echo "<a href='" . htmlspecialchars($url) . "' class='$class_str$edit_class'$edit_attr>" . htmlspecialchars($text) . "</a>";
}

/**
 * Gibt einen editierbaren Button aus
 */
function editable_button($key, $text, $class = 'btn btn-primary', $onclick = '') {
    global $EDIT_MODE;
    
    $edit_attr = '';
    $edit_class = '';
    
    if ($EDIT_MODE) {
        $edit_class = ' editable';
        $edit_attr = ' data-edit-key="' . htmlspecialchars($key) . '" data-type="text"';
    }
    
    $onclick_attr = $onclick ? " onclick='$onclick'" : '';
    
    echo "<button class='$class$edit_class'$edit_attr$onclick_attr>" . htmlspecialchars($text) . "</button>";
}

/**
 * Gibt einen Editor-Badge an, wenn im Edit-Mode
 */
function edit_badge($label = 'Edit me') {
    global $EDIT_MODE;
    if ($EDIT_MODE) {
        echo '<span class="edit-badge">✏️ ' . htmlspecialchars($label) . '</span>';
    }
}

/**
 * Wrapper für ganze Sections
 */
function editable_section($key, $content, $tag = 'div', $class = []) {
    global $EDIT_MODE;
    
    $class_str = implode(' ', $class);
    $edit_attr = '';
    $edit_class = '';
    
    if ($EDIT_MODE) {
        $edit_class = ' editable-section';
        $edit_attr = ' data-edit-key="' . htmlspecialchars($key) . '" data-type="html"';
    }
    
    echo "<$tag class='$class_str$edit_class'$edit_attr>$content</$tag>";
}

// CSS für Edit-Badges
?>
<style>
.edit-badge {
    display: inline-block;
    background: #ffc107;
    color: #333;
    padding: 0.2rem 0.5rem;
    border-radius: 3px;
    font-size: 0.75rem;
    font-weight: 600;
    margin-left: 0.5rem;
}

.editable-section:hover {
    background: rgba(255, 193, 7, 0.05) !important;
    border: 1px dashed #ffc107 !important;
    padding: 1rem !important;
}
</style>