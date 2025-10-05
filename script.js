// Navbar scroll effect
window.addEventListener('scroll', function() {
    const navbar = document.getElementById('navbar');
    const scrolled = window.scrollY > 50;
    
    if (scrolled) {
        navbar.classList.add('scrolled');
    } else {
        navbar.classList.remove('scrolled');
    }
});

// Mobile menu toggle
const hamburger = document.getElementById('hamburger');
const navMenu = document.getElementById('nav-menu');
const menuOverlay = document.getElementById('menu-overlay');
const closeMenu = document.getElementById('close-menu');

function openMobileMenu() {
    navMenu.classList.add('active');
    menuOverlay.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeMobileMenu() {
    navMenu.classList.remove('active');
    menuOverlay.classList.remove('active');
    hamburger.classList.remove('active');
    document.body.style.overflow = 'auto';
}

hamburger.addEventListener('click', function() {
    openMobileMenu();
});

// Close menu when clicking close button
closeMenu.addEventListener('click', function() {
    closeMobileMenu();
});

// Close menu when clicking overlay
menuOverlay.addEventListener('click', function() {
    closeMobileMenu();
});

// Close mobile menu when clicking on a link
const navLinks = document.querySelectorAll('.nav-link');
navLinks.forEach(link => {
    link.addEventListener('click', () => {
        closeMobileMenu();
    });
});

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
});

// Resize event listener for responsive adjustments
window.addEventListener('resize', function() {
    // Close mobile menu on resize
    if (window.innerWidth > 768) {
        navMenu.classList.remove('active');
        hamburger.classList.remove('active');
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
            
            // Get form data
            const formData = {
                name: document.getElementById('contactName').value,
                businessName: document.getElementById('businessName').value,
                email: document.getElementById('contactEmail').value,
                phone: document.getElementById('phoneNumber').value,
                privacyAccepted: document.getElementById('privacyCheck').checked
            };
            
            // Basic validation
            if (!formData.privacyAccepted) {
                alert('Please accept the privacy policy to continue.');
                return;
            }
            
            // Show success message (you can replace this with actual form submission)
            const submitBtn = document.querySelector('.contact-submit-btn');
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
    
// Toggle Additional Projects Functionality
function toggleAdditionalProjects() {
    const additionalProjects = document.getElementById('additional-projects');
    const btn = document.getElementById('more-projects-btn');
    const btnText = document.getElementById('btn-text');
    const btnIcon = document.getElementById('btn-icon');
    
    if (additionalProjects.classList.contains('client-case-hidden')) {
        // Show the additional project
        additionalProjects.classList.remove('client-case-hidden');
        additionalProjects.classList.add('client-case-showing');
        
        // Update button
        btnText.textContent = 'Show less';
        btnIcon.classList.remove('fa-chevron-down');
        btnIcon.classList.add('fa-chevron-up');
        btn.classList.add('expanded');
        
        // Smooth scroll to make sure the new content is visible
        setTimeout(() => {
            additionalProjects.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'nearest' 
            });
        }, 200);
        
    } else {
        // Hide the additional project
        additionalProjects.classList.remove('client-case-showing');
        additionalProjects.classList.add('client-case-hidden');
        
        // Update button
        btnText.textContent = 'More projects';
        btnIcon.classList.remove('fa-chevron-up');
        btnIcon.classList.add('fa-chevron-down');
        btn.classList.remove('expanded');
    }
}
