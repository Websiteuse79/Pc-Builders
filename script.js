document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const mobileMenuBtn = document.querySelector('.mobile-menu');
    const nav = document.querySelector('nav');

    if (!mobileMenuBtn) console.warn('Mobile menu button (.mobile-menu) not found');
    if (!nav) console.warn('Nav element not found');

    if (mobileMenuBtn && nav) {
        mobileMenuBtn.setAttribute('role', 'button');
        mobileMenuBtn.setAttribute('aria-expanded', 'false');
        mobileMenuBtn.addEventListener('click', function() {
            try {
                nav.classList.toggle('active');
                const isActive = nav.classList.contains('active');
                mobileMenuBtn.innerHTML = isActive ? '<i class="fas fa-times"></i>' : '<i class="fas fa-bars"></i>';
                mobileMenuBtn.setAttribute('aria-expanded', isActive ? 'true' : 'false');
                console.log('Mobile menu toggled. active=', isActive);
            } catch (err) {
                console.error('Error toggling mobile menu', err);
            }
        });
    }
    
    // Smooth scrolling for navigation links
    if (document.querySelectorAll) {
        document.querySelectorAll('nav a').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const targetId = this.getAttribute('href') || '';
                if (targetId.startsWith('#')) {
                    e.preventDefault();
                    const targetElement = document.querySelector(targetId);
                    if (targetElement) {
                        window.scrollTo({
                            top: targetElement.offsetTop - 70,
                            behavior: 'smooth'
                        });
                    }
                    // Close mobile menu if open
                    if (nav && nav.classList && nav.classList.contains('active') && mobileMenuBtn) {
                        nav.classList.remove('active');
                        mobileMenuBtn.innerHTML = '<i class="fas fa-bars"></i>';
                    }
                }
                // For other links, let them work normally
            });
        });
    }
    
    // Header scroll effect
    const header = document.querySelector('header');
    if (header) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    }
    
    // Scroll animation
    const animateOnScrollElements = document.querySelectorAll('.animate-on-scroll');
    
    function checkScroll() {
        animateOnScrollElements.forEach(element => {
            const elementTop = element.getBoundingClientRect().top;
            const windowHeight = window.innerHeight;
            
            if (elementTop < windowHeight - 100) {
                element.classList.add('animate-on-scroll');
            }
        });
    }
    
    // Initial check
    checkScroll();
    
    // Check on scroll
    window.addEventListener('scroll', checkScroll);
    
    // Cart functionality
    const cartIcon = document.querySelector('.cart-icon');
    const cartSidebar = document.querySelector('.cart-sidebar');
    const closeCart = document.querySelector('.close-cart');
    const overlay = document.createElement('div');
    overlay.className = 'overlay';
    document.body.appendChild(overlay);

    // --- Cart sync from backend on page load ---
    // Cart data
    let cart = [];
    let cartTotal = 0;

    function syncCartFromBackend() {
        fetch('cart.php?action=get', { credentials: 'same-origin' })
            .then(res => res.json())
            .then(data => {
                if (data && Array.isArray(data.cart)) {
                    cart = data.cart.map(item => ({
                        name: item.name,
                        price: Number(item.price),
                        quantity: Number(item.quantity),
                        image: item.image || ''
                    }));
                    updateCartCount();
                    updateCartTotal();
                    renderCartItems();
                }
            })
            .catch(() => {
                // fallback: keep cart empty
            });
    }

    // Call sync on page load
    syncCartFromBackend();

    // Profile Sidebar Functionality.
    const profileIconSidebar = document.getElementById('navbarProfileIcon');
    const sidebarProfileSidebar = document.getElementById('profileSidebar');
    const sidebarProfileOverlay = document.getElementById('profileOverlay');
    const sidebarCloseProfile = document.getElementById('closeProfile');

    if (profileIconSidebar && sidebarProfileSidebar && sidebarProfileOverlay) {
        profileIconSidebar.addEventListener('click', function(e) {
            e.preventDefault();
            sidebarProfileSidebar.classList.add('active');
            sidebarProfileOverlay.style.display = 'block';
        });
        sidebarCloseProfile.addEventListener('click', function() {
            sidebarProfileSidebar.classList.remove('active');
            sidebarProfileOverlay.style.display = 'none';
        });
        sidebarProfileOverlay.addEventListener('click', function() {
            sidebarProfileSidebar.classList.remove('active');
            sidebarProfileOverlay.style.display = 'none';
        });
    }

    // Checkout modal logic
    const checkoutBtn = document.querySelector('.checkout-btn');
    const checkoutModal = document.getElementById('checkoutModal');
    const closeCheckoutModal = document.getElementById('closeCheckoutModal');
    const checkoutForm = document.getElementById('checkoutForm');
    const checkoutCartDetails = document.getElementById('checkoutCartDetails');
    const checkoutTotal = document.getElementById('checkoutTotal');
    const checkoutMsg = document.getElementById('checkoutMsg');

    function renderCheckoutCartDetails() {
        if (!checkoutCartDetails) return;
        if (cart.length === 0) {
            checkoutCartDetails.innerHTML = '<p>Your cart is empty</p>';
            checkoutTotal.textContent = '0';
            return;
        }
        let html = '<ul style="padding-left:18px;">';
        cart.forEach(item => {
            html += `<li>${escapeHtml(item.name)} x ${item.quantity} - ₹${(item.price * item.quantity).toLocaleString('en-IN')}</li>`;
        });
        html += '</ul>';
        checkoutCartDetails.innerHTML = html;
        checkoutTotal.textContent = cartTotal.toLocaleString('en-IN');
    }

    if (checkoutBtn && checkoutModal) {
        checkoutBtn.addEventListener('click', async function () {
            // Check login status from PHP-injected JS variable
            var isLoggedIn = typeof window.isLoggedIn !== 'undefined' ? window.isLoggedIn : false;
            if (!isLoggedIn) {
                alert('Please login to place an order.');
                window.location.href = 'Login/login.php';
                return;
            }
            // Always sync cart before checking
            await new Promise(resolve => {
                fetch('cart.php?action=get', { credentials: 'same-origin' })
                    .then(res => res.json())
                    .then(data => {
                        if (data && Array.isArray(data.cart)) {
                            cart = data.cart.map(item => ({
                                name: item.name,
                                price: Number(item.price),
                                quantity: Number(item.quantity),
                                image: item.image || ''
                            }));
                            updateCartCount();
                            updateCartTotal();
                            renderCartItems();
                        }
                        resolve();
                    })
                    .catch(() => resolve());
            });
            if (cart.length === 0) {
                alert('Your cart is empty.');
                return;
            }
            // Reset form and error
            checkoutForm.reset();
            checkoutMsg.textContent = '';
            renderCheckoutCartDetails();
            checkoutModal.style.display = 'flex';
        });
    }

    if (closeCheckoutModal && checkoutModal) {
        closeCheckoutModal.addEventListener('click', function() {
            checkoutModal.style.display = 'none';
        });
    }

    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            e.preventDefault();
            checkoutMsg.textContent = '';
            // Prevent double submit
            if (checkoutForm.classList.contains('submitting')) return;
            checkoutForm.classList.add('submitting');
            if (checkoutBtn) checkoutBtn.disabled = true;

            // Validate required fields
            const name = checkoutForm.name.value.trim();
            const phone = checkoutForm.phone.value.trim();
            const address = checkoutForm.address.value.trim();
            if (!name || !phone || !address) {
                checkoutMsg.textContent = 'Please fill in all required fields.';
                checkoutForm.classList.remove('submitting');
                if (checkoutBtn) checkoutBtn.disabled = false;
                return;
            }
            if (!/^\d{10,}$/.test(phone)) {
                checkoutMsg.textContent = 'Please enter a valid phone number.';
                checkoutForm.classList.remove('submitting');
                if (checkoutBtn) checkoutBtn.disabled = false;
                return;
            }
            // Prepare items
            const items = cart.map(item => ({ name: item.name, quantity: item.quantity, price: item.price }));
            // Prevent empty cart order
            if (!items.length || cartTotal <= 0) {
                checkoutMsg.textContent = 'Your cart is empty.';
                checkoutForm.classList.remove('submitting');
                if (checkoutBtn) checkoutBtn.disabled = false;
                return;
            }
            // Send order
            fetch('save_order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `name=${encodeURIComponent(name)}&phone=${encodeURIComponent(phone)}&address=${encodeURIComponent(address)}&items=${encodeURIComponent(JSON.stringify(items))}&total=${encodeURIComponent(cartTotal)}`
            })
            .then(response => response.text())
            .then(result => {
                // Only clear cart and redirect if order was successful
                try {
                    const data = JSON.parse(result);
                    if (data && data.success) {
                        // Clear cart in backend
                        fetch('cart.php?action=clear', { method: 'POST', credentials: 'same-origin' });
                        cart = [];
                        updateCartCount();
                        updateCartTotal();
                        renderCartItems();
                        window.location.href = 'thank_you.php';
                    } else {
                        checkoutMsg.textContent = 'Order failed.';
                    }
                } catch (e) {
                    window.location.href = 'thank_you.php'; // fallback
                }
            })
            .catch(error => {
                checkoutMsg.textContent = 'Error saving order.';
            })
            .finally(() => {
                checkoutForm.classList.remove('submitting');
                if (checkoutBtn) checkoutBtn.disabled = false;
            });
        });
    }
    if (cartIcon && cartSidebar) {
        cartIcon.addEventListener('click', function() {
            cartSidebar.classList.add('active');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
    }

    if (closeCart && cartSidebar) {
        closeCart.addEventListener('click', function() {
            cartSidebar.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        });
    }

    overlay.addEventListener('click', function() {
        if (cartSidebar) cartSidebar.classList.remove('active');
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    });

    // helper to escape html when injecting
    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
    
    // (cart and cartTotal now declared above for global use)
    
    // Update cart count
    function updateCartCount() {
        const count = cart.reduce((total, item) => total + item.quantity, 0);
        const el = document.querySelector('.cart-count');
        if (el) el.textContent = count;
    }
    
    // Update cart total
    function updateCartTotal() {
        cartTotal = cart.reduce((total, item) => total + (item.price * item.quantity), 0);
        const el = document.querySelector('.total-amount');
        if (el) el.textContent = cartTotal.toFixed(2);
    }
    
    // Render cart items
    function renderCartItems() {
        const cartItemsContainer = document.querySelector('.cart-items');
        if (!cartItemsContainer) return;
        cartItemsContainer.innerHTML = '';
        
        if (cart.length === 0) {
            cartItemsContainer.innerHTML = '<p>Your cart is empty</p>';
            return;
        }
        
        cart.forEach((item, index) => {
            const cartItem = document.createElement('div');
            cartItem.className = 'cart-item';
            cartItem.innerHTML = `
                <div class="cart-item-image">
                    <img src="${item.image || 'assets/images/default-component.png'}" alt="${item.name}">
                </div>
                <div class="cart-item-info">
                    <h4>${item.name}</h4>
                    <div class="cart-item-price">₹${item.price.toFixed(2)}</div>
                    <div class="cart-item-quantity">
                        <button class="decrease-quantity" data-index="${index}">-</button>
                        <span>${item.quantity}</span>
                        <button class="increase-quantity" data-index="${index}">+</button>
                    </div>
                    <button class="remove-item" data-index="${index}">Remove</button>
                </div>
            `;
            cartItemsContainer.appendChild(cartItem);
        });



        
        // Add event listeners to quantity buttons
        document.querySelectorAll('.decrease-quantity').forEach(button => {
            button.addEventListener('click', function() {
                const index = parseInt(this.getAttribute('data-index'));
                const item = cart[index];
                if (item.quantity > 1) {
                    // Update backend
                    fetch('cart.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `action=update&name=${encodeURIComponent(item.name)}&quantity=${item.quantity - 1}`,
                        credentials: 'same-origin'
                    }).then(() => {
                        syncCartFromBackend();
                    });
                } else {
                    // Remove from backend
                    fetch('cart.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `action=remove&name=${encodeURIComponent(item.name)}`,
                        credentials: 'same-origin'
                    }).then(() => {
                        syncCartFromBackend();
                    });
                }
            });
        });

        document.querySelectorAll('.increase-quantity').forEach(button => {
            button.addEventListener('click', function() {
                const index = parseInt(this.getAttribute('data-index'));
                const item = cart[index];
                // Update backend
                fetch('cart.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=update&name=${encodeURIComponent(item.name)}&quantity=${item.quantity + 1}`,
                    credentials: 'same-origin'
                }).then(() => {
                    syncCartFromBackend();
                });
            });
        });

        document.querySelectorAll('.remove-item').forEach(button => {
            button.addEventListener('click', function() {
                const index = parseInt(this.getAttribute('data-index'));
                const item = cart[index];
                // Remove from backend
                fetch('cart.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=remove&name=${encodeURIComponent(item.name)}`,
                    credentials: 'same-origin'
                }).then(() => {
                    syncCartFromBackend();
                });
            });
        });
    }




    // Profile sidebar functionality
const profileIcon = document.getElementById('profileIcon');
const profileSidebar = document.getElementById('profileSidebar');
const closeProfile = document.getElementById('closeProfile');
const profileOverlay = document.getElementById('profileOverlay');
const logoutBtn = document.getElementById('logoutBtn');

// Open sidebar
if (profileIcon) {
    profileIcon.addEventListener('click', () => {
        profileSidebar.classList.add('active');
        profileOverlay.style.display = 'block';
        document.body.style.overflow = 'hidden';
    });
}

// Close sidebar
function closeProfileSidebar() {
    profileSidebar.classList.remove('active');
    profileOverlay.style.display = 'none';
    document.body.style.overflow = 'auto';
}

if (closeProfile) {
    closeProfile.addEventListener('click', closeProfileSidebar);
}

if (profileOverlay) {
    profileOverlay.addEventListener('click', closeProfileSidebar);
}

// Close sidebar when pressing Escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeProfileSidebar();
    }
});

// Logout functionality
if (logoutBtn) {
    logoutBtn.addEventListener('click', () => {
        if (confirm('Are you sure you want to logout?')) {
            // Redirect to logout page
            window.location.href = 'logout.php';
        }
    });
}
    
    
    

    

    // (checkout handler attached earlier with guard)


    
    
    // Pre-build PC slider
    const prebuildSlider = document.querySelector('.slider');
    const prebuildPrev = document.querySelector('.slider-prev');
    const prebuildNext = document.querySelector('.slider-next');
    let currentSlide = 0;
    
    // Pre-build PC data will be loaded from database
    let prebuilds = [];
    
    // Render pre-build PCs from fetched data
    function renderPrebuilds() {
        prebuildSlider.innerHTML = '';
        if (!prebuilds.length) {
            prebuildSlider.innerHTML = '<p>No Pre-Build PCs found.</p>';
            return;
        }
        prebuilds.forEach((prebuild, index) => {
            const specsArr = prebuild.specs ? (Array.isArray(prebuild.specs) ? prebuild.specs : (typeof prebuild.specs === 'string' ? prebuild.specs.split(';') : [])) : [];
            const prebuildItem = document.createElement('div');
            prebuildItem.className = 'prebuild-item';
            prebuildItem.innerHTML = `
                <div class="prebuild-info">
                    <h3 style='text-align:center;'>${prebuild.name}</h3>
                </div>
                <div class="prebuild-image">
                    <img src="${prebuild.image || 'assets/images/default-component.png'}" alt="${prebuild.name}">
                </div>
                <div class="prebuild-info">
                    <p style='text-align:center;'>${prebuild.description || ''}</p>
                    <div class="prebuild-specs">
                        ${specsArr.map(spec => `<span style='font-weight:bold;'>${spec.trim()}</span>`).join('')}
                    </div>
                    <div class="prebuild-price">
                        <div class="price">₹${Number(prebuild.price).toLocaleString('en-IN')}</div>
                        <button class="btn add-to-cart" data-index="${index}">Add to Cart</button>
                    </div>
                </div>
            `;
            prebuildSlider.appendChild(prebuildItem);
        });
        // Add event listeners to add to cart buttons
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', function() {
                const index = parseInt(this.getAttribute('data-index'));
                const prebuild = prebuilds[index];
                // Add to backend cart
                fetch('cart.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=add&name=${encodeURIComponent(prebuild.name)}&price=${encodeURIComponent(prebuild.price)}&quantity=1&image=${encodeURIComponent(prebuild.image || '')}`,
                    credentials: 'same-origin'
                })
                .then(() => {
                    // Sync JS cart from backend after add
                    syncCartFromBackend();
                    // Show cart sidebar
                    cartSidebar.classList.add('active');
                    overlay.classList.add('active');
                    document.body.style.overflow = 'hidden';
                });
            });
        });
    }
    
    // Slider navigation (guarded)
    if (prebuildPrev) {
        prebuildPrev.addEventListener('click', function() {
            if (currentSlide > 0) {
                currentSlide--;
                updateSliderPosition();
            }
        });
    }

    if (prebuildNext) {
        prebuildNext.addEventListener('click', function() {
            const itemElem = document.querySelector('.prebuild-item');
            if (!itemElem || !prebuildSlider || !prebuildSlider.parentElement) return;
            const itemWidth = itemElem.offsetWidth + 20;
            const visibleItems = Math.floor(prebuildSlider.parentElement.offsetWidth / itemWidth);
            if (currentSlide < prebuilds.length - visibleItems) {
                currentSlide++;
                updateSliderPosition();
            }
        });
    }
    
    function updateSliderPosition() {
        const itemElem = document.querySelector('.prebuild-item');
        if (!itemElem || !prebuildSlider) return;
        const itemWidth = itemElem.offsetWidth + 20;
        prebuildSlider.style.transform = `translateX(-${currentSlide * itemWidth}px)`;
    }
    
    // Custom PC builder
    const buildSteps = document.querySelectorAll('.build-steps .step');
    const componentSelector = document.querySelector('.component-selector');
    const buildSummary = document.querySelector('.build-summary');
    const buildTotalElement = document.querySelector('.build-total');
    
    // Dynamic component categories and data
    let components = {};
    let selectedComponents = {};
    let buildStepOrder = [];

    // Fetch all component categories from server
    function fetchComponentsAndInitBuild() {
        fetch('fetch_components.php')
            .then(res => res.json())
            .then(data => {
                components = data;
                buildStepOrder = Object.keys(components);
                // Initialize selectedComponents
                buildStepOrder.forEach(cat => {
                    selectedComponents[cat] = null;
                });
                renderBuildSteps();
                showComponentCategory(buildStepOrder[0]);
            })
            
            .catch(() => {
                componentSelector.innerHTML = '<p style="color:red;">Could not load components. Please try again later.</p>';
            });
    }

    // Render build steps dynamically
    function renderBuildSteps() {
    const buildStepsContainer = document.querySelector('.build-steps');
    if (!buildStepsContainer) return;
    buildStepsContainer.innerHTML = '';
    buildStepsContainer.style.display = 'flex';
    buildStepsContainer.style.justifyContent = 'center';
    buildStepsContainer.style.flexWrap = 'wrap';
        buildStepOrder.forEach((cat, idx) => {
            const step = document.createElement('div');
            step.className = 'step' + (idx === 0 ? ' active' : '');
            step.setAttribute('data-step', cat);
            step.style.display = 'flex';
            step.style.flexDirection = 'column';
            step.style.alignItems = 'center';
            step.style.margin = '0 15px 10px 15px';
            step.innerHTML = `<span style="width:40px;height:40px;background:#e2e8f0;border-radius:50%;display:flex;align-items:center;justify-content:center;margin-bottom:5px;font-weight:600;">${idx + 1}</span><p style="font-weight:600;color:#94a3b8;">${cat.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</p>`;
            step.addEventListener('click', function() {
                document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
                this.classList.add('active');
                showComponentCategory(cat);
            });
            buildStepsContainer.appendChild(step);
        });
    }

    // Show component category
    function showComponentCategory(category) {
        if (!componentSelector) return;
        componentSelector.innerHTML = `
            <h3>Select ${category.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</h3>
            <div class="component-list"></div>
        `;
        const componentList = document.querySelector('.component-list');
        if (!componentList) return;
        if (!components[category] || !components[category].length) {
            componentList.innerHTML = '<p>No components found for this category.</p>';
            return;
        }
        // Get sort order and sort components by price
        let sortOrder = 'low-high';
        const sortSelect = document.getElementById('sortPrice');
        if (sortSelect) {
            sortOrder = sortSelect.value;
        }
        let sortedComponents = [...components[category]];
        sortedComponents.sort((a, b) => {
            let priceA = String(a.price).replace(/[^\d.]/g, '');
            let priceB = String(b.price).replace(/[^\d.]/g, '');
            priceA = Number(priceA) || 0;
            priceB = Number(priceB) || 0;
            if (sortOrder === 'low-high') {
                return priceA - priceB;
            } else if (sortOrder === 'high-low') {
                return priceB - priceA;
            } else {
                return 0;
            }
        });
        sortedComponents.forEach((component, index) => {
            const componentItem = document.createElement('div');
            componentItem.className = 'component-item';
            if (selectedComponents[category] && selectedComponents[category].id == component.id) {
                componentItem.classList.add('selected');
            }
            componentItem.innerHTML = `
                <h4>${component.name}</h4>
                <p>${component.description || ''}</p>
                <div class="component-price">₹${Number(component.price).toLocaleString('en-IN')}</div>
            `;
            componentItem.addEventListener('click', function() {
                document.querySelectorAll('.component-item').forEach(item => item.classList.remove('selected'));
                this.classList.add('selected');
                selectedComponents[category] = component;
                updateBuildSummary();
            });
            componentList.appendChild(componentItem);
        });
    }

    // Attach sort change listener (guarded) and set default to low-high
    const sortSelectGlobal = document.getElementById('sortPrice');
    if (sortSelectGlobal) {
        // ensure default selection is low-high on page load
        try { sortSelectGlobal.value = 'low-high'; } catch (e) { /* ignore */ }
        sortSelectGlobal.addEventListener('change', function() {
            const activeStep = document.querySelector('.step.active');
            let activeCategory = buildStepOrder[0];
            if (activeStep) activeCategory = activeStep.getAttribute('data-step');
            showComponentCategory(activeCategory);
        });
    }

    // Update build summary
    function updateBuildSummary() {
        const summaryItems = document.querySelector('.summary-items');
        if (!summaryItems) return;
        summaryItems.innerHTML = '';
        let total = 0;
        for (const [category, component] of Object.entries(selectedComponents)) {
            const summaryItem = document.createElement('div');
            summaryItem.className = 'summary-item';
            if (component) {
                summaryItem.innerHTML = `
                    <span style="font-weight:bold;">${category.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</span>
                    <span>${component.name}</span>
                    <span>₹${Number(component.price).toLocaleString('en-IN')}</span>
                `;
                total += Number(component.price);
            } else {
                summaryItem.innerHTML = `
                    <span style="font-weight:bold;">${category.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</span>
                    <span style="color:#bbb;">Not selected</span>
                    <span>-</span>
                `;
            }
            summaryItems.appendChild(summaryItem);
        }
        if (buildTotalElement) buildTotalElement.textContent = total.toLocaleString('en-IN');
    }

    // Add custom build to cart
    const addToCartBtn = document.querySelector('.add-to-cart-build');
    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', function() {
            const allSelected = Object.values(selectedComponents).every(component => component !== null);
            if (!allSelected) {
                alert('Please select all components before adding to cart');
                return;
            }
            const totalPrice = Object.values(selectedComponents).reduce((sum, component) => sum + Number(component.price), 0);
            // Add to backend cart
            fetch('cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=add&name=${encodeURIComponent('Custom PC Build')}&price=${encodeURIComponent(totalPrice)}&quantity=1&image=${encodeURIComponent('assets/images/custom-pc.png')}`,
                credentials: 'same-origin'
            })
            .then(() => {
                // Sync JS cart from backend after add
                syncCartFromBackend();
                cartSidebar.classList.add('active');
                overlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            });
        });
    }

    // Initialize build section
    // Always initialize build section if componentSelector exists
    if (componentSelector) {
        fetchComponentsAndInitBuild();
    }

    // Fetch prebuilds from server and initialize
    function fetchPrebuildsAndInit() {
        if (!prebuildSlider) return;
        fetch('fetch_prebuilds.php')
            .then(res => res.json())
            .then(data => {
                if (Array.isArray(data)) {
                    // normalize and sort by price ascending (low to high)
                    prebuilds = data.map(p => ({ ...p, price: Number(String(p.price).replace(/[^\d.]/g, '')) || 0 }));
                    prebuilds.sort((a, b) => a.price - b.price);
                    renderPrebuilds();
                } else if (data && data.error) {
                    prebuilds = [];
                    prebuildSlider.innerHTML = `<p style='color:red;'>Error: ${data.error}</p>`;
                } else {
                    prebuilds = [];
                    renderPrebuilds();
                }
            })
            .catch((err) => {
                prebuilds = [];
                prebuildSlider.innerHTML = `<p style='color:red;'>Could not fetch Pre-Build PCs. Please try again later.</p>`;
            });
    }
    if (prebuildSlider) {
        fetchPrebuildsAndInit();
    }

    // Only call showComponentCategory if componentSelector exists and categories are loaded
    // This should be handled after fetching components, so remove this redundant call.

    // Responsive adjustments
    window.addEventListener('resize', function() {
        if (typeof updateSliderPosition === 'function') updateSliderPosition();
    });
});
