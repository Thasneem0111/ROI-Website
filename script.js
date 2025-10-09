// Navbar scroll effect (init on load and on scroll)
function updateNavbarOnScroll() {
    const navbar = document.getElementById('navbar');
    if (!navbar) return;
    const scrolled = window.scrollY > 50;
    if (scrolled) {
        navbar.classList.add('scrolled');
    } else {
        navbar.classList.remove('scrolled');
    }
}
window.addEventListener('scroll', updateNavbarOnScroll);
window.addEventListener('DOMContentLoaded', updateNavbarOnScroll);

// Mobile menu toggle
const hamburger = document.getElementById('hamburger');
const navMenu = document.getElementById('nav-menu');
const menuOverlay = document.getElementById('menu-overlay');
const closeMenu = document.getElementById('close-menu');

function openMobileMenu() {
    if (navMenu) navMenu.classList.add('active');
    if (menuOverlay) menuOverlay.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeMobileMenu() {
    if (navMenu) navMenu.classList.remove('active');
    if (menuOverlay) menuOverlay.classList.remove('active');
    if (hamburger) hamburger.classList.remove('active');
    document.body.style.overflow = 'auto';
}

if (hamburger) {
    hamburger.addEventListener('click', function() {
        openMobileMenu();
    });
}

// Close menu when clicking close button
if (closeMenu) {
    closeMenu.addEventListener('click', function() {
        closeMobileMenu();
    });
}

// Close menu when clicking overlay
if (menuOverlay) {
    menuOverlay.addEventListener('click', function() {
        closeMobileMenu();
    });
}

// Close mobile menu when clicking on a link
const navLinks = document.querySelectorAll('.nav-link');
let navNavigating = false;
function handleNavActivate(link, e) {
    if (navNavigating) return;
    const href = link.getAttribute('href') || '';
    // In-page hash links: smooth scroll after closing menu
    if (href.startsWith('#')) {
        if (e) e.preventDefault();
        if (e && typeof e.stopImmediatePropagation === 'function') e.stopImmediatePropagation();
        closeMobileMenu();
        const target = document.querySelector(href);
        if (target) {
            const offsetTop = target.offsetTop - 80;
            setTimeout(() => {
                window.scrollTo({ top: offsetTop, behavior: 'smooth' });
            }, 150);
        }
        return;
    }
    // Other links: close the menu and force navigation to avoid mobile/browser quirks
    if (href) {
        // Let browser navigate; also add a fallback in case navigation is swallowed
        navNavigating = true;
        const before = window.location.href;
        const targetUrl = link.href; // fully resolved absolute
        closeMobileMenu();
        // Fallback: if still on same URL shortly after, force navigation
        setTimeout(() => {
            if (window.location.href === before) {
                try { window.location.assign(targetUrl); } catch(_) { window.location.href = targetUrl; }
            }
        }, 120);
        return;
    }
}

// Use click only to avoid double-firing on touch devices
navLinks.forEach(link => {
    link.addEventListener('click', (e) => handleNavActivate(link, e));
    link.addEventListener('touchend', (e) => handleNavActivate(link, e), { passive: false });
});

// Event delegation fallback for mobile menu to ensure taps always navigate
if (navMenu) {
    const delegatedHandler = (e) => {
        const a = e.target && e.target.closest ? e.target.closest('a.nav-link') : null;
        if (!a) return;
        handleNavActivate(a, e);
    };
    navMenu.addEventListener('click', delegatedHandler);
    navMenu.addEventListener('touchend', delegatedHandler, { passive: false });
}

// Document-level capture fallback: if default navigation didn't happen (some mobile quirks), force it
document.addEventListener('click', function(e) {
    const a = e.target && e.target.closest ? e.target.closest('a.nav-link') : null;
    if (!a) return;
    const href = a.getAttribute('href') || '';
    if (!href || href.startsWith('#')) return; // hash handled elsewhere
    const current = window.location.href;
    // Only fallback if menu is open (overlay active) to avoid interfering with normal clicks
    const overlayActive = !!(document.getElementById('menu-overlay')?.classList.contains('active'));
    setTimeout(() => {
        if (overlayActive && window.location.href === current) {
            try {
                const abs = new URL(href, current).href;
                window.location.href = abs;
            } catch(_) {
                window.location.href = href;
            }
        }
    }, 120);
}, { capture: true });

// Smooth scrolling for navigation links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            const offsetTop = target.offsetTop - 80; // Account for fixed navbar
            window.scrollTo({
                top: offsetTop,
                behavior: 'smooth'
            });
        }
    });
});

// Intersection Observer for scroll animations
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver(function(entries) {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('animate');
        }
    });
}, observerOptions);

// Initialize hero animations and interactions
document.addEventListener('DOMContentLoaded', function() {
    // Initialize chart bars with dynamic heights
    initializeChartBars();
    
    // Add floating element interactions
    initializeFloatingElements();
    
    // Initialize other animations
    const animateElements = document.querySelectorAll('.hero-left, .hero-main-image');
    animateElements.forEach(el => observer.observe(el));
});

// Initialize chart bars with data-height attributes
function initializeChartBars() {
    const chartBars = document.querySelectorAll('.chart-bar');
    chartBars.forEach(bar => {
        const height = bar.getAttribute('data-height');
        if (height) {
            bar.style.height = height + '%';
        }
    });
}

// Initialize floating element interactions
function initializeFloatingElements() {
    const floatingCards = document.querySelectorAll('.floating-card');
    const floatingBadges = document.querySelectorAll('.floating-badge');
    
    // Add subtle floating animation after initial load
    setTimeout(() => {
        floatingCards.forEach((card, index) => {
            setInterval(() => {
                const randomDelay = Math.random() * 2000;
                setTimeout(() => {
                    card.style.transform = 'translateY(-3px)';
                    setTimeout(() => {
                        card.style.transform = 'translateY(0px)';
                    }, 1000);
                }, randomDelay);
            }, 4000 + (index * 500));
        });
        
        floatingBadges.forEach((badge, index) => {
            setInterval(() => {
                const randomDelay = Math.random() * 1500;
                setTimeout(() => {
                    badge.style.transform = 'translateY(-2px) scale(1.02)';
                    setTimeout(() => {
                        badge.style.transform = 'translateY(0px) scale(1)';
                    }, 800);
                }, randomDelay);
            }, 3500 + (index * 400));
        });
    }, 3000);
}

// Subtle parallax effect for floating elements
window.addEventListener('scroll', function() {
    const scrolled = window.pageYOffset;
    const heroFloatingElements = document.querySelectorAll('.floating-card, .floating-badge');
    
    if (scrolled < 500) { // Only apply when near hero section
        heroFloatingElements.forEach((element, index) => {
            const speed = 0.02 + (index * 0.01);
            const yPos = -(scrolled * speed);
            const currentTransform = element.style.transform || '';
            
            // Preserve existing transforms while adding parallax
            if (!currentTransform.includes('translateY')) {
                element.style.transform = `translateY(${yPos}px)`;
            }
        });
    }
});

// Add hover effects to buttons
const buttons = document.querySelectorAll('.hero-cta-btn, .search-btn, .cta-btn');
buttons.forEach(button => {
    button.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-3px)';
    });
    
    button.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0)';
    });
});

// Add click animation to floating elements
const floatingElements = document.querySelectorAll('.floating-element');
floatingElements.forEach(element => {
    element.addEventListener('click', function() {
        this.style.transform = 'scale(0.95)';
        setTimeout(() => {
            this.style.transform = 'scale(1)';
        }, 150);
    });
});

// Counter animation for project count
function animateCounter() {
    const counter = document.querySelector('.count-number');
    const target = 120;
    let current = 0;
    const increment = target / 60; // 60 frames for smooth animation
    
    const updateCounter = () => {
        if (current < target) {
            current += increment;
            counter.textContent = Math.floor(current) + '+';
            requestAnimationFrame(updateCounter);
        } else {
            counter.textContent = target + '+';
        }
    };
    
    // Start counter animation when element is visible
    const counterObserver = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                updateCounter();
                counterObserver.unobserve(entry.target);
            }
        });
    });
    
    if (counter) {
        counterObserver.observe(counter.parentElement);
    }
}

// Chart bars animation
function animateChartBars() {
    const bars = document.querySelectorAll('.bar');
    bars.forEach((bar, index) => {
        setTimeout(() => {
            bar.style.transform = 'scaleY(1)';
            bar.style.opacity = '1';
        }, index * 100);
    });
}

// Typewriter effect for hero title
function typewriterEffect() {
    const title = document.querySelector('.hero-title');
    if (!title) return;
    
    const text = title.textContent;
    title.textContent = '';
    title.style.opacity = '1';
    
    let i = 0;
    const typeInterval = setInterval(() => {
        if (i < text.length) {
            title.textContent += text.charAt(i);
            i++;
        } else {
            clearInterval(typeInterval);
        }
    }, 50);
}

// Initialize animations on page load
document.addEventListener('DOMContentLoaded', function() {
    // Delay initial animations
    setTimeout(() => {
        animateCounter();
        animateChartBars();
    }, 1000);
    
    // Start typewriter effect after hero animation
    setTimeout(() => {
        typewriterEffect();
    }, 800);
});

// Add loading animation
window.addEventListener('load', function() {
    document.body.classList.add('loaded');
    // Ensure navbar state correct on initial load
    updateNavbarOnScroll();
});

// Resize event listener for responsive adjustments
window.addEventListener('resize', function() {
    // Close mobile menu on resize
    if (window.innerWidth > 768) {
        if (navMenu) navMenu.classList.remove('active');
        if (hamburger) hamburger.classList.remove('active');
        if (menuOverlay) menuOverlay.classList.remove('active');
        document.body.style.overflow = 'auto';
    }
});

// Add scroll-triggered animations for better performance
let ticking = false;

function updateAnimations() {
    const scrolled = window.pageYOffset;
    
    // Update parallax elements
    const parallaxElements = document.querySelectorAll('.floating-element');
    parallaxElements.forEach(element => {
        const speed = element.dataset.speed || 0.1;
        const yPos = -(scrolled * speed);
        element.style.transform = `translateY(${yPos}px)`;
    });
    
    ticking = false;
}

function requestTick() {
    if (!ticking) {
        requestAnimationFrame(updateAnimations);
        ticking = true;
    }
}

window.addEventListener('scroll', requestTick);

// Add touch support for mobile devices
if ('ontouchstart' in window) {
    document.addEventListener('touchstart', function() {}, {passive: true});
}

// Mobile-specific enhancements for clients section
document.addEventListener('DOMContentLoaded', function() {
    const clientsSection = document.querySelector('.clients-section');
    const clientsTrack = document.querySelector('.clients-track');
    
    // Optimize animation for mobile devices
    function optimizeForMobile() {
        const isMobile = window.innerWidth <= 768;
        
        if (isMobile) {
            // Faster animation on mobile for better performance
            clientsTrack.style.animationDuration = '12s';
            
            // Reduce animation complexity on slower devices
            if (navigator.hardwareConcurrency && navigator.hardwareConcurrency <= 4) {
                clientsTrack.style.animationDuration = '15s';
            }
        } else {
            // Desktop animation speed
            clientsTrack.style.animationDuration = '20s';
        }
    }
    
    // Touch interaction for mobile
    let startX = 0;
    let currentX = 0;
    let isDragging = false;
    
    if (clientsSection) {
        // Touch start
        clientsSection.addEventListener('touchstart', function(e) {
            startX = e.touches[0].clientX;
            isDragging = true;
            // Pause animation during touch
            clientsTrack.style.animationPlayState = 'paused';
        }, {passive: true});
        
        // Touch move
        clientsSection.addEventListener('touchmove', function(e) {
            if (!isDragging) return;
            
            currentX = e.touches[0].clientX;
            const deltaX = currentX - startX;
            
            // Optional: Add slight resistance effect
            if (Math.abs(deltaX) > 50) {
                // Add visual feedback for swipe
                clientsSection.style.transform = `translateX(${deltaX * 0.1}px)`;
            }
        }, {passive: true});
        
        // Touch end
        clientsSection.addEventListener('touchend', function() {
            isDragging = false;
            // Resume animation
            clientsTrack.style.animationPlayState = 'running';
            // Reset position
            clientsSection.style.transform = 'translateX(0)';
        });
    }
    
    // Initial optimization
    optimizeForMobile();
    
    // Re-optimize on resize
    window.addEventListener('resize', function() {
        clearTimeout(window.resizeTimeout);
        window.resizeTimeout = setTimeout(optimizeForMobile, 250);
    });
    
    // Intersection Observer for clients section
    const clientsObserver = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // Start animation when section is visible
                if (clientsTrack) {
                    clientsTrack.style.animationPlayState = 'running';
                }
            } else {
                // Pause animation when section is not visible (performance optimization)
                if (clientsTrack && window.innerWidth <= 768) {
                    clientsTrack.style.animationPlayState = 'paused';
                }
            }
        });
    }, {
        threshold: 0.1
    });
    
    if (clientsSection) {
        clientsObserver.observe(clientsSection);
    }
    
    // Preload client logo images for better performance
    const clientLogos = document.querySelectorAll('.client-logo img');
    clientLogos.forEach(img => {
        const imageLoader = new Image();
        imageLoader.src = img.src;
    });
    
    // Initialize Business Growth Chart
    initializeBusinessChart();
    
    // Initialize Hero Section Growth Chart
    initializeGrowthChart();
});

// Business Growth Chart
function initializeBusinessChart() {
    const ctx = document.getElementById('businessChart');
    const chartContainer = document.querySelector('.chart-wrapper');
    if (!ctx || !chartContainer) return;
    
    // Ensure container has proper dimensions
    chartContainer.style.position = 'relative';
    chartContainer.style.height = '350px';
    chartContainer.style.width = '100%';
    
    // Set canvas to fill container
    ctx.style.position = 'absolute';
    ctx.style.top = '0';
    ctx.style.left = '0';
    ctx.style.width = '100%';
    ctx.style.height = '100%';
    
    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct'],
            datasets: [
                {
                    label: 'Digital Marketing ROI',
                    data: [1, 2, 4, 3.5, 7.5, 7, 4.5, 3.5, 9.5, 8],
                    borderColor: '#20B2AA',
                    backgroundColor: 'rgba(32, 178, 170, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#20B2AA',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 3,
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    pointHoverBackgroundColor: '#FF6B35',
                    pointHoverBorderColor: '#ffffff',
                    pointHoverBorderWidth: 3
                },
                {
                    label: 'Traditional Marketing',
                    data: [0, 1, 2.5, 4.5, 3.5, 4.5, 5.5, 7, 6.5, 7.5],
                    borderColor: '#FF6B35',
                    backgroundColor: 'rgba(255, 107, 53, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#FF6B35',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 3,
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    pointHoverBackgroundColor: '#20B2AA',
                    pointHoverBorderColor: '#ffffff',
                    pointHoverBorderWidth: 3
                },
                {
                    label: 'Projected Growth',
                    data: [0, 0, 0, 0, 0, 2.5, 3, 2.5, 4, 5.5],
                    borderColor: '#40E0D0',
                    backgroundColor: 'rgba(64, 224, 208, 0.05)',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    fill: false,
                    tension: 0.4,
                    pointBackgroundColor: '#40E0D0',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            resizeDelay: 100,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: {
                            size: 12,
                            family: 'Inter'
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(255, 255, 255, 0.95)',
                    titleColor: '#333333',
                    bodyColor: '#666666',
                    borderColor: '#20B2AA',
                    borderWidth: 1,
                    cornerRadius: 8,
                    displayColors: true,
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': $' + context.parsed.y + 'k';
                        }
                    }
                }
            },
            scales: {
                x: {
                    display: true,
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 11,
                            family: 'Inter'
                        },
                        color: '#666666'
                    }
                },
                y: {
                    display: true,
                    beginAtZero: true,
                    max: 10,
                    grid: {
                        color: 'rgba(32, 178, 170, 0.1)',
                        drawBorder: false
                    },
                    ticks: {
                        font: {
                            size: 11,
                            family: 'Inter'
                        },
                        color: '#666666',
                        stepSize: 2,
                        callback: function(value) {
                            return value;
                        }
                    }
                }
            },
            elements: {
                point: {
                    hoverRadius: 8
                }
            },
            animation: {
                duration: 2000,
                easing: 'easeInOutQuart'
            }
        }
    });
    
    // Handle window resize
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function() {
            if (chart) {
                chart.resize();
            }
        }, 100);
    });
    
    // Force resize after initialization
    setTimeout(function() {
        if (chart) {
            chart.resize();
        }
    }, 100);
    
    // Add special highlight for peak value
    const peakAnnotation = {
        id: 'peakAnnotation',
        afterDatasetsDraw: function(chart) {
            const ctx = chart.ctx;
            const meta = chart.getDatasetMeta(0);
            const point = meta.data[8]; // Peak point at index 8
            
            if (point) {
                // Draw peak indicator
                ctx.save();
                ctx.fillStyle = '#FF6B35';
                ctx.font = 'bold 12px Inter';
                ctx.textAlign = 'center';
                ctx.fillText('$9.7k', point.x, point.y - 20);
                ctx.restore();
            }
        }
    };
    
    Chart.register(peakAnnotation);
    
    // Animate chart when it comes into view
    const chartObserver = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                chart.update('show');
                chartObserver.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.3
    });
    
    chartObserver.observe(ctx);
    
    return chart;
}

// Initialize Growth Chart for Hero Section
function initializeGrowthChart() {
    const ctx = document.getElementById('growthChart');
    if (!ctx) return;

    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Revenue',
                data: [65, 75, 85, 105, 115, 125],
                borderColor: '#20B2AA',
                backgroundColor: 'rgba(32, 178, 170, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#20B2AA',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 6,
                pointHoverRadius: 8,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff',
                    borderColor: '#20B2AA',
                    borderWidth: 1,
                    cornerRadius: 8,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return '$' + context.parsed.y + 'K';
                        }
                    }
                }
            },
            scales: {
                x: {
                    display: true,
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#6B7280',
                        font: {
                            size: 12,
                            weight: '500'
                        }
                    }
                },
                y: {
                    display: false,
                    grid: {
                        display: false
                    }
                }
            },
            elements: {
                point: {
                    hoverBackgroundColor: '#20B2AA'
                }
            },
            animation: {
                duration: 2000,
                easing: 'easeInOutQuart'
            }
        }
    });

    // Animate chart when it comes into view
    const chartObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                chart.update('show');
                chartObserver.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.3
    });
    
    chartObserver.observe(ctx);
    
    return chart;
}

// Trust tiles scroll-in animation
document.addEventListener('DOMContentLoaded', function() {
    const tiles = document.querySelectorAll('.trust-tile');
    if (!tiles.length) return;
    const io = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const el = entry.target;
                const delay = parseInt(el.getAttribute('data-delay') || '0', 10);
                setTimeout(() => el.classList.add('in-view'), Math.max(0, delay));
                io.unobserve(el);
            }
        });
    }, { threshold: 0.15 });
    tiles.forEach(t => io.observe(t));
});

// FAQ Accordion Functionality
document.addEventListener('DOMContentLoaded', function() {
    const faqQuestions = document.querySelectorAll('.faq-question');
    
    faqQuestions.forEach(question => {
        question.addEventListener('click', function() {
            const faqNumber = this.getAttribute('data-faq');
            const answer = document.getElementById(`faq-${faqNumber}`);
            const isActive = this.classList.contains('active');
            
            // Close all other FAQ items
            faqQuestions.forEach(q => {
                q.classList.remove('active');
                const num = q.getAttribute('data-faq');
                const ans = document.getElementById(`faq-${num}`);
                ans.classList.remove('active');
            });
            
            // Toggle current FAQ item
            if (!isActive) {
                this.classList.add('active');
                answer.classList.add('active');
            }
        });
    });
});

// Contact Form Submission
document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            // Support both index and contact page field IDs
            const getVal = (ids) => {
                for (const id of ids) {
                    const el = document.getElementById(id);
                    if (el) return el.type === 'checkbox' ? el.checked : el.value;
                }
                return '';
            };
            const formData = {
                name: getVal(['contactName', 'name']),
                businessName: getVal(['businessName']),
                email: getVal(['contactEmail', 'email']),
                phone: getVal(['phoneNumber', 'phone']),
                privacyAccepted: getVal(['privacyCheck', 'privacyPolicy'])
            };
            if (!formData.privacyAccepted) {
                alert('Please accept the privacy policy to continue.');
                return;
            }
            const submitBtn = document.querySelector('.contact-submit-btn');
            if (!submitBtn) return;
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Sending...';
            submitBtn.disabled = true;
            setTimeout(() => {
                submitBtn.textContent = 'Message Sent!';
                setTimeout(() => {
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                    contactForm.reset();
                }, 2000);
            }, 1000);
        });
    }
});
    
// Toggle Additional Projects Functionality (generic: show first 2, toggle the rest)
function toggleAdditionalProjects() {
    const container = document.getElementById('client-cases-container');
    const btn = document.getElementById('more-projects-btn');
    const btnText = document.getElementById('btn-text');
    const btnIcon = document.getElementById('btn-icon');
    if (!container || !btn || !btnText) return;

    const items = Array.from(container.querySelectorAll('.client-case-item'));
    const visibleCount = 2;
    if (items.length <= visibleCount) return; // nothing to toggle

    const extras = items.slice(visibleCount);
    const isCollapsed = extras.some(el => el.classList.contains('client-case-hidden'));

    if (isCollapsed) {
        // Expand: show all extra items
        extras.forEach(el => {
            el.classList.remove('client-case-hidden');
            el.classList.add('client-case-showing');
        });
        btnText.textContent = 'Show less';
        if (btnIcon) { btnIcon.classList.remove('fa-chevron-down'); btnIcon.classList.add('fa-chevron-up'); }
        btn.classList.add('expanded');
        // Smooth scroll to the first newly shown item
        setTimeout(() => { extras[0]?.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); }, 200);
    } else {
        // Collapse: hide all extra items
        extras.forEach(el => {
            el.classList.remove('client-case-showing');
            el.classList.add('client-case-hidden');
        });
        btnText.textContent = 'More projects';
        if (btnIcon) { btnIcon.classList.remove('fa-chevron-up'); btnIcon.classList.add('fa-chevron-down'); }
        btn.classList.remove('expanded');
    }
}

// Ensure only first 2 client cases are visible on load
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('client-cases-container');
    if (!container) return;
    const items = Array.from(container.querySelectorAll('.client-case-item'));
    const visibleCount = 2;
    items.forEach((el, i) => {
        if (i < visibleCount) {
            el.classList.remove('client-case-hidden');
        } else {
            el.classList.add('client-case-hidden');
            el.classList.remove('client-case-showing');
        }
    });
    const btnText = document.getElementById('btn-text');
    const btn = document.getElementById('more-projects-btn');
    if (btnText) btnText.textContent = 'More projects';
    if (btn) {
        btn.classList.remove('expanded');
        if (items.length <= visibleCount) {
            btn.style.display = 'none';
        } else {
            btn.style.display = '';
        }
    }
});

// Newsletter Subscribe handler (shared)
function handleSubscribe(e) {
    try {
        if (e) e.preventDefault();
        const form = e && e.target ? e.target : document.getElementById('subscribeForm');
        const emailInput = form ? form.querySelector('input[type="email"]') : null;
        const success = document.getElementById('subscribeSuccess');
        if (!emailInput) return false;
        const email = emailInput.value.trim();
        if (!email) { emailInput.focus(); return false; }
        if (success) {
            success.hidden = false;
            success.style.opacity = '0';
            requestAnimationFrame(() => {
                success.style.transition = 'opacity 240ms ease';
                success.style.opacity = '1';
            });
        }
        form.reset();
    } catch (_) {
        // no-op
    }
    return false;
}

// ===================== Global Consultation Modal Logic ===================== //
// This enables every "Book A Free Consultation" button/link across any page to open the unified modal.
(function(){
    if (window.__consultModalInit) return; // guard
    window.__consultModalInit = true;

    function ensureModalPresent(){
        if(document.getElementById('consultModal')) return;
        // Minimal CSS injection if not already on index page
        if(!document.getElementById('consultModalStyles')){
            const style = document.createElement('style');
            style.id='consultModalStyles';
            style.textContent = `
            .consult-modal{position:fixed;inset:0;display:flex;align-items:center;justify-content:center;padding:clamp(1rem,2vw,2rem);background:rgba(17,24,39,.55);backdrop-filter:blur(2px);z-index:1000;opacity:0;pointer-events:none;transition:opacity .25s ease;font-family:Inter,system-ui,sans-serif;}
            .consult-modal[aria-hidden="false"]{opacity:1;pointer-events:auto;}
            .consult-modal-dialog{background:#fff;width:100%;max-width:480px;border-radius:20px;padding:2rem 1.75rem 2.25rem;position:relative;box-shadow:0 10px 35px -5px rgba(0,0,0,.25);outline:none;}
            .consult-modal-close{position:absolute;top:.75rem;right:.75rem;border:none;background:#f1f5f9;color:#0f172a;width:38px;height:38px;border-radius:50%;font-size:1.35rem;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:background .18s,color .18s;}
            .consult-modal-close:hover{background:#e2e8f0;color:#020617;}
            .consult-modal-title{margin:0 0 .35rem;font-size:1.55rem;line-height:1.25;font-weight:650;color:#0f172a;letter-spacing:.5px;}
            .consult-modal-intro{margin:0 0 1.4rem;color:#475569;font-size:.95rem;}
            #consultForm .form-row{margin-bottom:1rem;display:flex;flex-direction:column;gap:.4rem;}
            #consultForm .form-row.actions{flex-direction:row;justify-content:center;align-items:center;margin-top:.35rem;margin-bottom:.25rem;}
            #consultForm label{font-weight:550;font-size:.84rem;text-transform:uppercase;letter-spacing:.75px;color:#1e293b;}
            #consultForm input{border:2px solid #e2e8f0;border-radius:11px;padding:.78rem .9rem;font-size:.95rem;background:#f8fafc;transition:border-color .18s,background .18s;}
            #consultForm input:focus{outline:none;background:#fff;border-color:#0ea5e9;box-shadow:0 0 0 4px rgba(14,165,233,.15);}
            #consultForm input.invalid{border-color:#dc2626;background:#fff1f1;}
            .field-error{color:#dc2626;font-size:.7rem;min-height:14px;}
            .consult-submit-btn{background:linear-gradient(135deg,#0d9488,#0f766e);border:none;color:#fff;padding:.95rem 1.35rem;font-weight:600;border-radius:50px;font-size:.95rem;cursor:pointer;display:inline-flex;align-items:center;gap:.55rem;transition:background .2s,transform .2s,box-shadow .2s;}
            .consult-submit-btn:hover{transform:translateY(-3px);box-shadow:0 8px 18px -4px rgba(13,148,136,.45);}
            .consult-submit-btn:disabled{opacity:.65;cursor:not-allowed;}
            .spinner{--d:20px;display:inline-block;width:var(--d);height:var(--d);border:3px solid rgba(255,255,255,.35);border-top-color:#fff;border-radius:50%;animation:spin .75s linear infinite;}
            @keyframes spin{to{transform:rotate(360deg)}}
            .form-success{margin-top:.75rem;padding:.85rem 1rem;border-radius:12px;background:linear-gradient(115deg,#0f766e,#0d9488);font-size:.83rem;color:#fff;display:flex;align-items:center;gap:.65rem;letter-spacing:.4px;}
            .form-success .success-icon{background:#fff;color:#0f766e;font-size:.86rem;width:22px;height:22px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:650;}
            .no-scroll{overflow:hidden!important;touch-action:none;}
            @media (max-width:460px){.consult-modal-dialog{padding:1.6rem 1.25rem 1.9rem;}.consult-modal-title{font-size:1.35rem;}}
            `;
            document.head.appendChild(style);
        }
        const template = `\n<div class="consult-modal" id="consultModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="consultModalTitle">\n  <div class="consult-modal-backdrop" data-close-modal></div>\n  <div class="consult-modal-dialog" role="document" tabindex="-1">\n    <button class="consult-modal-close" type="button" data-close-modal aria-label="Close">&times;</button>\n    <h2 class="consult-modal-title" id="consultModalTitle">Book A Free Consultation</h2>\n    <p class="consult-modal-intro">Fill in your details and our team will reach out shortly.</p>\n    <form id="consultForm" novalidate>\n      <div class="form-row">\n        <label for="cfName">Name <span class="req">*</span></label>\n        <input id="cfName" name="name" type="text" required maxlength="80" autocomplete="name" />\n        <small class="field-error" data-error-for="cfName"></small>\n      </div>\n      <div class="form-row">\n        <label for="cfEmail">Email address <span class="req">*</span></label>\n        <input id="cfEmail" name="email" type="email" required maxlength="120" autocomplete="email" />\n        <small class="field-error" data-error-for="cfEmail"></small>\n      </div>\n      <div class="form-row">\n        <label for="cfPhone">Contact number <span class="req">*</span></label>\n        <input id="cfPhone" name="phone" type="tel" required maxlength="30" autocomplete="tel" placeholder="+974 5xxxxxxx" />\n        <small class="field-error" data-error-for="cfPhone"></small>\n      </div>\n      <div class="form-row actions">\n        <button type="submit" class="consult-submit-btn" id="cfSubmit">Submit</button>\n      </div>\n      <div class="form-success" id="cfSuccess" role="alert" aria-live="polite" hidden>\n        <span class="success-icon">✓</span> Thank you! We received your request.\n      </div>\n    </form>\n  </div>\n</div>`;
        document.body.insertAdjacentHTML('beforeend', template);
    }

    function bindModalLogic(){
        const modal = document.getElementById('consultModal');
        if(!modal || modal.dataset.bound) return;
        modal.dataset.bound = 'true';
        const dialog = modal.querySelector('.consult-modal-dialog');
        const closeEls = modal.querySelectorAll('[data-close-modal]');
        let lastFocus;
        function openModal(){
            lastFocus = document.activeElement;
            modal.setAttribute('aria-hidden','false');
            document.body.classList.add('no-scroll');
            setTimeout(()=> dialog && dialog.focus(), 15);
        }
        function closeModal(){
            modal.setAttribute('aria-hidden','true');
            document.body.classList.remove('no-scroll');
            if(lastFocus) try{ lastFocus.focus(); }catch(_){ }
        }
        modal.__openConsultModal = openModal; // expose globally for triggers
        closeEls.forEach(el=> el.addEventListener('click', closeModal));
        modal.addEventListener('click', e=>{ if(e.target.classList.contains('consult-modal-backdrop')) closeModal(); });
        document.addEventListener('keydown', e=>{ if(e.key==='Escape' && modal.getAttribute('aria-hidden')==='false') closeModal(); });

        // Form wiring
        const form = document.getElementById('consultForm');
        if(form && !form.dataset.bound){
            form.dataset.bound='true';
            const success = document.getElementById('cfSuccess');
            const submitBtn = document.getElementById('cfSubmit');
            const spinnerHTML = '<span class="spinner" aria-hidden="true"></span>';
            form.addEventListener('submit', async e=>{
                e.preventDefault();
                if(success) success.hidden = true;
                let valid = true;
                const setErr=(field,msg)=>{
                    const box = form.querySelector(`[data-error-for="${field.id}"]`);
                    if(box) box.textContent = msg||'';
                    field.classList.toggle('invalid', !!msg);
                    if(msg && valid){ field.focus(); valid=false; }
                };
                const name = form.querySelector('#cfName');
                const email = form.querySelector('#cfEmail');
                const phone = form.querySelector('#cfPhone');
                setErr(name, !name.value.trim()? 'Name is required':'' );
                const emailVal = email.value.trim();
                const emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailVal);
                setErr(email, !emailVal? 'Email is required': (!emailOk? 'Enter a valid email':''));
                const phoneVal = phone.value.trim();
                const phoneOk = /^[+()\d\s-]{7,}$/.test(phoneVal);
                setErr(phone, !phoneVal? 'Phone is required': (!phoneOk? 'Enter a valid phone number':''));
                if(!valid) return;
                form.classList.add('submitting');
                submitBtn.disabled = true;
                const originalBtnTxt = submitBtn.textContent;
                submitBtn.innerHTML = spinnerHTML + originalBtnTxt;
                try {
                    const apiBase = (location.port === '3000') ? '' : 'http://localhost:3000';
                    const resp = await fetch(apiBase + '/api/consultation', {
                        method:'POST',
                        headers:{ 'Content-Type':'application/json' },
                        body: JSON.stringify({ name: name.value.trim(), email: emailVal, phone: phoneVal })
                    });
                    if(!resp.ok) {
                        let data; try { data = await resp.json(); } catch(_) { data = {}; }
                        throw new Error(data.message || ('Failed (HTTP '+resp.status+')'));
                    }
                    form.reset();
                    if(success) success.hidden = false;
                } catch(err){
                    alert('Error: '+ err.message);
                } finally {
                    form.classList.remove('submitting');
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalBtnTxt;
                }
            });
        }
    }

    function bindTriggers(){
        const triggers = Array.from(document.querySelectorAll('a,button,[role="button"]')).filter(el=>{
            if(el.dataset.consultTrigger) return true;
            const txt = (el.textContent||'').trim();
            if(!txt) return false;
            return /book\s+a\s+free\s+consultation/i.test(txt);
        });
        triggers.forEach(el=>{
            if(el.dataset.consultBound) return;
            el.dataset.consultBound='true';
            el.addEventListener('click', function(e){
                // Skip if inside another form that isn't the consult modal form
                if(el.closest('#consultForm')) return; // already inside modal
                if(el.closest('#contactForm')){ e.preventDefault(); } // override contact form button
                if(el.tagName==='A'){ e.preventDefault(); }
                const modal = document.getElementById('consultModal');
                if(modal && modal.__openConsultModal){ modal.__openConsultModal(); }
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function(){
        ensureModalPresent();
        bindModalLogic();
        bindTriggers();
    });
})();
// =================== End Global Consultation Modal Logic ==================== //
