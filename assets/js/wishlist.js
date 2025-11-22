/**
 * WISHLIST.JS - Xử lý tính năng yêu thích sản phẩm
 */

// Lấy SITE_URL động từ PHP (nếu có), fallback về gốc
const SITE_URL = window.SITE_URL || '';

/**
 * Toggle wishlist (Thêm/Xóa sản phẩm yêu thích)
 */
function toggleWishlist(productId, button) {
    // Kiểm tra đăng nhập
    if (!isUserLoggedIn()) {
        showToast('warning', 'Chưa đăng nhập', 'Vui lòng đăng nhập để thêm sản phẩm yêu thích!');
        setTimeout(() => {
            window.location.href = SITE_URL + '/login.php?redirect=' + encodeURIComponent(window.location.pathname);
        }, 1500);
        return;
    }
    
    // Thêm class loading
    button.classList.add('loading');
    
    // Kiểm tra trạng thái hiện tại
    const isActive = button.classList.contains('active');
    const action = isActive ? 'remove' : 'add';
    
    // Gửi AJAX request
    fetch(SITE_URL + '/wishlist-handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=${action}&product_id=${productId}`
    })
    .then(response => response.json())
    .then(data => {
        button.classList.remove('loading');
        
        if (data.success) {
            // Toggle active state
            button.classList.toggle('active');
            button.classList.add('animating');
            
            // Remove animation class after animation ends
            setTimeout(() => {
                button.classList.remove('animating');
            }, 600);
            
            // Show notification
            if (action === 'add') {
                showToast('success', 'Đã thêm vào yêu thích', data.message);
            } else {
                showToast('info', 'Đã xóa khỏi yêu thích', data.message);
            }
        } else {
            showToast('error', 'Lỗi', data.message || 'Có lỗi xảy ra!');
        }
    })
    .catch(error => {
        button.classList.remove('loading');
        console.error('Wishlist error:', error);
        showToast('error', 'Lỗi', 'Không thể kết nối đến server!');
    });
}

/**
 * Kiểm tra user đã đăng nhập chưa
 */
function isUserLoggedIn() {
    // Kiểm tra bằng cách gọi API hoặc check cookie/session
    // Ở đây đơn giản hóa bằng cách check xem có user info trong page không
    return document.querySelector('[data-user-logged-in]') !== null;
}

/**
 * Load wishlist count (optional - để hiển thị số lượng yêu thích)
 */
function loadWishlistCount() {
    if (!isUserLoggedIn()) return;
    
    fetch(SITE_URL + '/wishlist-handler.php?action=count')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.count > 0) {
                const wishlistIcon = document.querySelector('.wishlist-count');
                if (wishlistIcon) {
                    wishlistIcon.textContent = data.count;
                    wishlistIcon.style.display = 'inline-block';
                }
            }
        })
        .catch(error => console.error('Load wishlist count error:', error));
}

/**
 * Remove from wishlist (dùng trong trang wishlist.php)
 */
function removeFromWishlist(productId, element) {
    if (!confirm('Bạn có chắc muốn xóa sản phẩm này khỏi danh sách yêu thích?')) {
        return;
    }
    
    fetch(SITE_URL + '/wishlist-handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=remove&product_id=${productId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove element from DOM
            const productCard = element.closest('.product-card');
            if (productCard) {
                productCard.style.opacity = '0';
                productCard.style.transform = 'scale(0.8)';
                setTimeout(() => {
                    productCard.remove();
                    
                    // Check if wishlist is empty
                    const remainingProducts = document.querySelectorAll('.product-card').length;
                    if (remainingProducts === 0) {
                        location.reload(); // Reload to show empty state
                    }
                }, 300);
            }
            
            showToast('success', 'Thành công', data.message);
        } else {
            showToast('error', 'Lỗi', data.message);
        }
    })
    .catch(error => {
        console.error('Remove wishlist error:', error);
        showToast('error', 'Lỗi', 'Có lỗi xảy ra khi xóa sản phẩm!');
    });
}

// Load wishlist count on page load
document.addEventListener('DOMContentLoaded', function() {
    loadWishlistCount();
});