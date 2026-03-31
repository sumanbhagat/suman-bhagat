<?php include '../backend/config.php'; ?>
<?php include 'includes/header.php'; ?>

<?php
require_once '../backend/admin/database/connection.php';

$db = getDB();

// Get gallery categories
$stmt = $db->query("SELECT DISTINCT category FROM gallery_images WHERE is_active = 1 ORDER BY category");
$categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Get active gallery images
$category_filter = $_GET['category'] ?? '';
$sql = "SELECT * FROM gallery_images WHERE is_active = 1";
$params = [];

if ($category_filter) {
    $sql .= " AND category = ?";
    $params[] = $category_filter;
}

$sql .= " ORDER BY display_order ASC, created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$images = $stmt->fetchAll();
?>

<!-- Gallery Hero -->
<section class="hero" style="min-height: 50vh;">
    <div class="container">
        <div class="hero-content">
            <div class="hero-text">
                <h1>Photo <span>Gallery</span></h1>
                <p class="subtitle">Moments Captured Through My Lens</p>
                <p>A collection of photos from my travels, events, and creative projects. Click on any image to view it in full size.</p>
            </div>
        </div>
    </div>
</section>

<!-- Gallery Content -->
<section class="gallery-section" style="padding-top: 0;">
    <div class="container">
        <div class="section-header">
            <h2>My <span>Collection</span></h2>
            <p>Click on any image to view it in full size</p>
        </div>
        
        <!-- Gallery Categories -->
        <div style="text-align: center; margin-bottom: 40px;">
            <a href="?" class="btn <?php echo empty($category_filter) ? 'btn-primary' : 'btn-secondary'; ?>" style="margin: 5px;">All Photos</a>
            <?php foreach ($categories as $cat): ?>
            <a href="?category=<?php echo urlencode($cat); ?>" class="btn <?php echo $category_filter === $cat ? 'btn-primary' : 'btn-secondary'; ?>" style="margin: 5px;"><?php echo $cat; ?></a>
            <?php endforeach; ?>
        </div>
        
        <!-- Gallery Grid -->
        <div class="gallery-grid">
            <?php foreach ($images as $image): ?>
            <div class="gallery-item" onclick="openLightbox('<?php echo $image['file_path']; ?>', '<?php echo htmlspecialchars($image['title']); ?>')">
                <img src="<?php echo $image['file_path']; ?>" alt="<?php echo htmlspecialchars($image['title']); ?>" onerror="this.src='https://via.placeholder.com/400x400/6366f1/ffffff?text=Image'">
                <div class="gallery-overlay">
                    <i class="fas fa-search-plus"></i>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php if (empty($images)): ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 60px; color: #6b7280;">
                <i class="fas fa-images" style="font-size: 4rem; margin-bottom: 20px; display: block;"></i>
                <p>No images yet. Upload some from the portfolio admin!</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Video Section -->
<section style="background: var(--bg-light); padding: 80px 0;">
    <div class="container">
        <div class="section-header">
            <h2>Video <span>Gallery</span></h2>
            <p>Short clips from events and travels</p>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
            <div style="background: var(--bg-white); border-radius: var(--radius); overflow: hidden; box-shadow: var(--shadow);">
                <div style="position: relative; padding-bottom: 56.25%; background: #000;">
                    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: flex; align-items: center; justify-content: center; color: white;">
                        <i class="fas fa-play-circle" style="font-size: 4rem; opacity: 0.8;"></i>
                    </div>
                </div>
                <div style="padding: 20px;">
                    <h4 style="margin-bottom: 5px;">Travel Vlog: Japan</h4>
                    <p style="color: var(--text-light); font-size: 0.9rem;">2:30 min</p>
                </div>
            </div>
            
            <div style="background: var(--bg-white); border-radius: var(--radius); overflow: hidden; box-shadow: var(--shadow);">
                <div style="position: relative; padding-bottom: 56.25%; background: #000;">
                    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: flex; align-items: center; justify-content: center; color: white;">
                        <i class="fas fa-play-circle" style="font-size: 4rem; opacity: 0.8;"></i>
                    </div>
                </div>
                <div style="padding: 20px;">
                    <h4 style="margin-bottom: 5px;">Tech Conference 2025</h4>
                    <p style="color: var(--text-light); font-size: 0.9rem;">5:45 min</p>
                </div>
            </div>
            
            <div style="background: var(--bg-white); border-radius: var(--radius); overflow: hidden; box-shadow: var(--shadow);">
                <div style="position: relative; padding-bottom: 56.25%; background: #000;">
                    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: flex; align-items: center; justify-content: center; color: white;">
                        <i class="fas fa-play-circle" style="font-size: 4rem; opacity: 0.8;"></i>
                    </div>
                </div>
                <div style="padding: 20px;">
                    <h4 style="margin-bottom: 5px;">Behind the Scenes</h4>
                    <p style="color: var(--text-light); font-size: 0.9rem;">3:15 min</p>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function openLightbox(imageSrc, title) {
    // Create lightbox
    const lightbox = document.createElement('div');
    lightbox.style.cssText = 'position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.9);display:flex;align-items:center;justify-content:center;z-index:10000;padding:20px;cursor:pointer;';
    
    const img = document.createElement('img');
    img.src = imageSrc;
    img.style.cssText = 'max-width:90%;max-height:90%;border-radius:8px;box-shadow:0 20px 50px rgba(0,0,0,0.5);';
    
    const caption = document.createElement('div');
    caption.textContent = title;
    caption.style.cssText = 'position:absolute;bottom:30px;left:0;right:0;text-align:center;color:white;font-size:1.2rem;text-shadow:0 2px 4px rgba(0,0,0,0.5);';
    
    const closeBtn = document.createElement('span');
    closeBtn.innerHTML = '&times;';
    closeBtn.style.cssText = 'position:absolute;top:20px;right:30px;font-size:3rem;color:white;cursor:pointer;text-shadow:0 2px 4px rgba(0,0,0,0.5);';
    
    lightbox.appendChild(img);
    if (title) lightbox.appendChild(caption);
    lightbox.appendChild(closeBtn);
    
    document.body.appendChild(lightbox);
    document.body.style.overflow = 'hidden';
    
    // Close handlers
    const close = () => {
        lightbox.remove();
        document.body.style.overflow = '';
    };
    
    lightbox.addEventListener('click', close);
    closeBtn.addEventListener('click', (e) => { e.stopPropagation(); close(); });
    document.addEventListener('keydown', function escHandler(e) {
        if (e.key === 'Escape') {
            close();
            document.removeEventListener('keydown', escHandler);
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>
