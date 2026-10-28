<?php
$file = "change_primary_assign.php";
$content = file_get_contents($file);

$replace1_old = '// --- Fetch Letters for Dropdown ---
$stmt = $pdo->query("SELECT id, letter_no, subject, sender_name, sender_designation, sender_institute FROM letters ORDER BY id DESC");
$letters = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>';
$replace1_new = '// --- Fetch Letters for Dropdown ---
$stmt = $pdo->query("SELECT id, letter_no, subject, sender_name, sender_designation, sender_institute FROM letters ORDER BY id DESC");
$letters = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- Fetch Section Heads for Dropdown ---
$stmt_sh = $pdo->query("SELECT name FROM section_heads ORDER BY id ASC");
$section_heads_list = $stmt_sh->fetchAll(PDO::FETCH_COLUMN);
?>';

$replace2_new = '<select class="form-control" name="new_section_head" required>
                                <option value=""><?= $text[\'select_option\'] ?></option>
                                <?php foreach($section_heads_list as $sh): ?>
                                    <option value="<?= htmlspecialchars($sh) ?>"><?= htmlspecialchars($sh) ?></option>
                                <?php endforeach; ?>
                            </select>';

$content = str_replace($replace1_old, $replace1_new, $content);
$content = preg_replace('/<select class="form-control" name="new_section_head" required>.*?<\/select>/s', $replace2_new, $content);

file_put_contents($file, $content);
echo "Done";
?>
