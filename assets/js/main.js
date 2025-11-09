/**
 * MAIN.JS - JavaScript chính cho website Xanh Organic
 * 
 * Chức năng:
 * - AJAX thêm vào giỏ hàng
 * - Back to top button
 * - Mobile menu
 * - Dropdown menu
 */

// ===== BIẾN TOÀN CỤC =====
const SITE_URL = window.location.origin + '/xanhorganic'; // Điều chỉnh theo đường dẫn thực tế

// ===== HÀM THÊM SẢN PHẨM VÀO GIỎ HÀNG BẰNG AJAX =====
/**
 * Thêm sản phẩm vào giỏ hàng không reload trang
 * @param {number} productId - ID sản phẩm
 * @param {number} quantity - Số lượng (mặc định 1)
 */
function addToCart(productId, quantity = 1) {
    // Tạo FormData
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('quantity', quantity);
    
    // Hiển thị loading (optional)
    showLoading();
    
    // Gửi AJAX request
    fetch(SITE_URL + '/add-to-cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        
        if (data.success) {
            // Cập nhật số lượng giỏ hàng trên header
            updateCartCount(data.cart_count);
            
            // Hiển thị thông báo thành công
            showNotification('success', data.message || 'Đã thêm vào giỏ hàng!');
            
            // Optional: Hiển thị popup giỏ hàng
            // showCartPopup();
        } else {
            // Hiển thị thông báo lỗi
            showNotification('error', data.message || 'Có lỗi xảy ra!');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showNotification('error', 'Có lỗi xảy ra. Vui lòng thử lại!');
    });
}

// ===== CẬP NHẬT SỐ LƯỢNG GIỎ HÀNG =====
/**
 * Cập nhật số lượng hiển thị trên icon giỏ hàng
 * @param {number} count - Số lượng sản phẩm
 */
function updateCartCount(count) {
    const cartCountElement = document.querySelector('.cart-count');
    if (cartCountElement) {
        cartCountElement.textContent = count;
        
        // Animation bounce
        cartCountElement.style.transform = 'scale(1.3)';
        setTimeout(() => {
            cartCountElement.style.transform = 'scale(1)';
        }, 200);
    }
}

// ===== HIỂN THỊ THÔNG BÁO =====
/**
 * Hiển thị thông báo toast
 * @param {string} type - Loại thông báo: 'success', 'error', 'warning', 'info'
 * @param {string} message - Nội dung thông báo
 */
function showNotification(type, message) {
    // Tạo element thông báo
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            <span>${message}</span>
        </div>
    `;
    
    // Thêm vào body
    document.body.appendChild(notification);
    
    // Hiển thị với animation
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    // Tự động ẩn sau 3 giây
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}

// ===== LOADING SPINNER =====
let loadingElement = null;

function showLoading() {
    if (!loadingElement) {
        loadingElement = document.createElement('div');
        loadingElement.className = 'loading-overlay';
        loadingElement.innerHTML = '<div class="spinner"></div>';
        document.body.appendChild(loadingElement);
    }
    loadingElement.style.display = 'flex';
}

function hideLoading() {
    if (loadingElement) {
        loadingElement.style.display = 'none';
    }
}

// ===== BACK TO TOP BUTTON =====
document.addEventListener('DOMContentLoaded', function() {
    const backToTopBtn = document.getElementById('backToTop');
    
    if (backToTopBtn) {
        // Hiển thị/ẩn nút khi scroll
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopBtn.classList.add('show');
            } else {
                backToTopBtn.classList.remove('show');
            }
        });
        
        // Click để scroll lên top
        backToTopBtn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
});

// ===== MOBILE MENU TOGGLE =====
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const navMenu = document.querySelector('.nav-menu');
    
    if (mobileMenuToggle && navMenu) {
        mobileMenuToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            this.classList.toggle('active');
        });
    }
});

// ===== DROPDOWN MENU =====
document.addEventListener('DOMContentLoaded', function() {
    const dropdowns = document.querySelectorAll('.dropdown');
    
    dropdowns.forEach(dropdown => {
        const toggle = dropdown.querySelector('.dropdown-toggle');
        const menu = dropdown.querySelector('.dropdown-menu');
        
        if (toggle && menu) {
            // Click để toggle dropdown
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                dropdown.classList.toggle('active');
            });
            
            // Click outside để đóng dropdown
            document.addEventListener('click', function(e) {
                if (!dropdown.contains(e.target)) {
                    dropdown.classList.remove('active');
                }
            });
        }
    });
});

// ===== SEARCH BOX ANIMATION =====
document.addEventListener('DOMContentLoaded', function() {
    const searchBox = document.querySelector('.search-box input');
    
    if (searchBox) {
        searchBox.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        searchBox.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });
    }
});

// ===== IMAGE LAZY LOADING =====
document.addEventListener('DOMContentLoaded', function() {
    const images = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                observer.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
});

// ===== FORM VALIDATION =====
/**
 * Validate form trước khi submit
 * @param {HTMLFormElement} form - Form element
 * @returns {boolean}
 */
function validateForm(form) {
    const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('error');
            isValid = false;
        } else {
            input.classList.remove('error');
        }
    });
    
    return isValid;
}

// ===== AUTO-HIDE ALERTS =====
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, 5000);
    });
});

// ===== QUANTITY INPUT CONTROLS =====
document.addEventListener('DOMContentLoaded', function() {
    const quantityInputs = document.querySelectorAll('.quantity-input');
    
    quantityInputs.forEach(container => {
        const input = container.querySelector('input[type="number"]');
        const decreaseBtn = container.querySelector('button:first-child');
        const increaseBtn = container.querySelector('button:last-child');
        
        if (input && decreaseBtn && increaseBtn) {
            decreaseBtn.addEventListener('click', () => {
                const currentValue = parseInt(input.value) || 1;
                const minValue = parseInt(input.min) || 1;
                if (currentValue > minValue) {
                    input.value = currentValue - 1;
                    input.dispatchEvent(new Event('change'));
                }
            });
            
            increaseBtn.addEventListener('click', () => {
                const currentValue = parseInt(input.value) || 1;
                const maxValue = parseInt(input.max) || 999;
                if (currentValue < maxValue) {
                    input.value = currentValue + 1;
                    input.dispatchEvent(new Event('change'));
                }
            });
        }
    });
});

// ===== SMOOTH SCROLL FOR ANCHOR LINKS =====
document.addEventListener('DOMContentLoaded', function() {
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
});

// ===== CONSOLE LOG =====
console.log('Xanh Organic - Main.js loaded successfully ✓');