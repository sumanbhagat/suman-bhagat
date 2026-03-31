/**
 * Hero Slider - Beautiful UX Design
 * Auto-advancing with smooth transitions
 */

(function() {
    'use strict';
    
    const HeroSlider = {
        currentSlide: 0,
        slides: null,
        dots: null,
        progressBar: null,
        autoplayInterval: null,
        progressInterval: null,
        slideDuration: 5000, // 5 seconds per slide
        isPaused: false,
        
        init: function() {
            this.slides = document.querySelectorAll('.hero-slider .slide');
            this.dots = document.querySelectorAll('.slider-nav .dot');  // Fixed selector
            this.progressBar = null;  // Remove progress bar as it doesn't exist in HTML
            
            if (!this.slides.length) return;
            
            this.bindEvents();
            this.startAutoplay();
            this.updateProgress();
        },
        
        bindEvents: function() {
            const self = this;
            
            // Dot navigation
            this.dots.forEach((dot, index) => {
                dot.addEventListener('click', function() {
                    self.goToSlide(index);
                });
            });
            
            // Arrow navigation
            const prevBtn = document.querySelector('.slider-prev');  // Fixed selector
            const nextBtn = document.querySelector('.slider-next');   // Fixed selector
            
            if (prevBtn) {
                prevBtn.addEventListener('click', function() {
                    self.prevSlide();
                });
            }
            
            if (nextBtn) {
                nextBtn.addEventListener('click', function() {
                    self.nextSlide();
                });
            }
            
            // Pause on hover
            const slider = document.querySelector('.hero-slider');
            if (slider) {
                slider.addEventListener('mouseenter', function() {
                    self.pause();
                });
                
                slider.addEventListener('mouseleave', function() {
                    self.resume();
                });
            }
            
            // Keyboard navigation
            document.addEventListener('keydown', function(e) {
                if (e.key === 'ArrowLeft') {
                    self.prevSlide();
                } else if (e.key === 'ArrowRight') {
                    self.nextSlide();
                }
            });
            
            // Touch/swipe support
            let touchStartX = 0;
            let touchEndX = 0;
            
            if (slider) {
                slider.addEventListener('touchstart', function(e) {
                    touchStartX = e.changedTouches[0].screenX;
                }, { passive: true });
                
                slider.addEventListener('touchend', function(e) {
                    touchEndX = e.changedTouches[0].screenX;
                    self.handleSwipe(touchStartX, touchEndX);
                }, { passive: true });
            }
        },
        
        handleSwipe: function(startX, endX) {
            const threshold = 50;
            const diff = startX - endX;
            
            if (Math.abs(diff) > threshold) {
                if (diff > 0) {
                    this.nextSlide();
                } else {
                    this.prevSlide();
                }
            }
        },
        
        goToSlide: function(index) {
            if (index === this.currentSlide) return;
            
            // Remove active class from current slide
            this.slides[this.currentSlide].classList.remove('active');
            this.dots[this.currentSlide].classList.remove('active');
            
            // Add active class to new slide
            this.currentSlide = index;
            this.slides[this.currentSlide].classList.add('active');
            this.dots[this.currentSlide].classList.add('active');
            
            // Reset autoplay
            this.resetAutoplay();
        },
        
        nextSlide: function() {
            const next = (this.currentSlide + 1) % this.slides.length;
            this.goToSlide(next);
        },
        
        prevSlide: function() {
            const prev = (this.currentSlide - 1 + this.slides.length) % this.slides.length;
            this.goToSlide(prev);
        },
        
        startAutoplay: function() {
            const self = this;
            this.autoplayInterval = setInterval(function() {
                if (!self.isPaused) {
                    self.nextSlide();
                }
            }, this.slideDuration);
        },
        
        stopAutoplay: function() {
            clearInterval(this.autoplayInterval);
        },
        
        resetAutoplay: function() {
            this.stopAutoplay();
            this.startAutoplay();
        },
        
        pause: function() {
            this.isPaused = true;
        },
        
        resume: function() {
            this.isPaused = false;
            this.resetAutoplay();
        }
    };
    
    // Initialize slider when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            HeroSlider.init();
        });
    } else {
        HeroSlider.init();
    }
    
    // Expose to global scope for debugging
    window.HeroSlider = HeroSlider;
})();
