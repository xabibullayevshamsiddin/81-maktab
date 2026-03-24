
// ===== Source: script.js =====
(() => {
const navbar = document.getElementById('navbar');
const menuToggle = document.getElementById('menu-toggle');
const siteNav = document.getElementById('site-nav');
const navLinks = document.querySelectorAll('.nav-link');
const scrollTopBtn = document.getElementById('scroll-top');
const reveals = document.querySelectorAll('.reveal');
const likeButtons = document.querySelectorAll('.like-btn');
const contactForm = document.getElementById('contact-form');
const formMessage = document.getElementById('form-message');
const year = document.getElementById('year');

if (year) {
  year.textContent = String(new Date().getFullYear());
}

if (menuToggle && siteNav) {
  menuToggle.addEventListener('click', () => {
    const isOpen = siteNav.classList.toggle('open');
    menuToggle.setAttribute('aria-expanded', String(isOpen));
  });

  navLinks.forEach((link) => {
    link.addEventListener('click', () => {
      siteNav.classList.remove('open');
      menuToggle.setAttribute('aria-expanded', 'false');
    });
  });
}

const onScroll = () => {
  const scrollY = window.scrollY;

  if (navbar) {
    navbar.classList.toggle('scrolled', scrollY > 30);
  }

  if (scrollTopBtn) {
    scrollTopBtn.classList.toggle('show', scrollY > 320);
  }

  const fromTop = scrollY + 120;
  navLinks.forEach((link) => {
    const href = link.getAttribute('href');

    // faqat # bilan boshlansa (section link) ishlasin
    if (!href || !href.startsWith('#')) return;

    const section = document.querySelector(href);
    if (!section) return;

    const isActive =
      section.offsetTop <= fromTop &&
      section.offsetTop + section.offsetHeight > fromTop;

    link.classList.toggle('active', isActive);
  });
};

window.addEventListener('scroll', onScroll);
onScroll();

if (scrollTopBtn) {
  scrollTopBtn.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
}

if ('IntersectionObserver' in window) {
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          observer.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.18 }
  );

  reveals.forEach((item) => observer.observe(item));
} else {
  reveals.forEach((item) => item.classList.add('visible'));
}

likeButtons.forEach((button) => {
  button.addEventListener('click', () => {
    const icon = button.querySelector('i');
    const countEl = button.querySelector('.like-count');
    if (!countEl || !icon) return;

    const base = Number.parseInt(countEl.textContent || '0', 10);
    const liked = button.classList.toggle('liked');

    countEl.textContent = String(liked ? base + 1 : Math.max(0, base - 1));
    icon.classList.toggle('fa-regular', !liked);
    icon.classList.toggle('fa-solid', liked);
  });
});

if (contactForm) {
  contactForm.addEventListener('submit', (event) => {
    event.preventDefault();

    const name = document.getElementById('name')?.value.trim();
    const email = document.getElementById('email')?.value.trim();
    const message = document.getElementById('message')?.value.trim();

    if (!name || !email || !message) {
      formMessage.textContent = "Iltimos, barcha maydonlarni to'ldiring.";
      formMessage.style.color = '#b91c1c';
      return;
    }

    formMessage.textContent =
      "Xabaringiz qabul qilindi. Tez orada siz bilan bog'lanamiz.";
    formMessage.style.color = '#0f766e';
    contactForm.reset();
  });
}

})();

// ===== Source: about/about.js =====
(() => {
const navbar = document.getElementById('navbar');
const menuToggle = document.getElementById('menu-toggle');
const siteNav = document.getElementById('site-nav');
const navLinks = document.querySelectorAll('.nav-link');
const scrollTopBtn = document.getElementById('scroll-top');
const reveals = document.querySelectorAll('.reveal');
const year = document.getElementById('year');

if (year) {
  year.textContent = String(new Date().getFullYear());
}

if (menuToggle && siteNav) {
  menuToggle.addEventListener('click', () => {
    const isOpen = siteNav.classList.toggle('open');
    menuToggle.setAttribute('aria-expanded', String(isOpen));
  });

  navLinks.forEach((link) => {
    link.addEventListener('click', () => {
      siteNav.classList.remove('open');
      menuToggle.setAttribute('aria-expanded', 'false');
    });
  });
}

const onScroll = () => {
  const scrollY = window.scrollY;

  if (navbar) {
    navbar.classList.toggle('scrolled', scrollY > 30);
  }

  if (scrollTopBtn) {
    scrollTopBtn.classList.toggle('show', scrollY > 320);
  }
};

window.addEventListener('scroll', onScroll);
onScroll();

if (scrollTopBtn) {
  scrollTopBtn.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
}

if ('IntersectionObserver' in window) {
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          observer.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.18 }
  );

  reveals.forEach((item) => observer.observe(item));
} else {
  reveals.forEach((item) => item.classList.add('visible'));
}

})();

// ===== Source: contact/contact.js =====
(() => {
const navbar = document.getElementById('navbar');
const menuToggle = document.getElementById('menu-toggle');
const siteNav = document.getElementById('site-nav');
const navLinks = document.querySelectorAll('.nav-link');
const scrollTopBtn = document.getElementById('scroll-top');
const reveals = document.querySelectorAll('.reveal');
const contactForm = document.getElementById('contact-form');
const formMessage = document.getElementById('form-message');
const yearEl = document.getElementById('year');

if (yearEl) {
  yearEl.textContent = String(new Date().getFullYear());
}

if (menuToggle && siteNav) {
  menuToggle.addEventListener('click', () => {
    const isOpen = siteNav.classList.toggle('open');
    menuToggle.setAttribute('aria-expanded', String(isOpen));
  });

  navLinks.forEach((link) => {
    link.addEventListener('click', () => {
      siteNav.classList.remove('open');
      menuToggle.setAttribute('aria-expanded', 'false');
    });
  });
}

const onScroll = () => {
  const scrollY = window.scrollY;

  if (navbar) {
    navbar.classList.toggle('scrolled', scrollY > 30);
  }

  if (scrollTopBtn) {
    scrollTopBtn.classList.toggle('show', scrollY > 320);
  }
};

window.addEventListener('scroll', onScroll);
onScroll();

if (scrollTopBtn) {
  scrollTopBtn.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
}

if ('IntersectionObserver' in window) {
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          observer.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.16 }
  );

  reveals.forEach((item) => observer.observe(item));
} else {
  reveals.forEach((item) => item.classList.add('visible'));
}

if (contactForm) {
  contactForm.addEventListener('submit', (event) => {
    event.preventDefault();

    const name = document.getElementById('name')?.value.trim();
    const email = document.getElementById('email')?.value.trim();
    const message = document.getElementById('message')?.value.trim();

    if (!name || !email || !message) {
      formMessage.textContent = "Iltimos, barcha maydonlarni to'ldiring.";
      formMessage.style.color = '#b91c1c';
      return;
    }

    formMessage.textContent = "Xabaringiz qabul qilindi. Tez orada siz bilan bog'lanamiz.";
    formMessage.style.color = '#0f766e';
    contactForm.reset();
  });
}

})();

// ===== Source: courses/courses.js =====
(() => {
(function () {
    const filterBtns = document.querySelectorAll('.filter-btn');
    const cards = document.querySelectorAll('.course-card');
    const mobileToggleBtn = document.getElementById('mobile-show-courses');
    let allCoursesExpanded = false;
    let wasMobile = window.matchMedia('(max-width: 820px)').matches;

    function isMobileView() {
        return window.matchMedia('(max-width: 820px)').matches;
    }

    function setToggleText() {
        if (!mobileToggleBtn) return;
        if (allCoursesExpanded) {
            mobileToggleBtn.innerHTML = `Kurslarni yig'ish <i class="fa-solid fa-chevron-down"></i>`;
            mobileToggleBtn.classList.add('expanded');
        } else {
            mobileToggleBtn.innerHTML = `Barcha kurslar <i class="fa-solid fa-chevron-down"></i>`;
            mobileToggleBtn.classList.remove('expanded');
        }
    }

    function applyMobileCollapse() {
        cards.forEach(card => card.classList.remove('mobile-collapsed-hidden'));

        if (!mobileToggleBtn) return;

        const visibleCards = Array.from(cards).filter(card => !card.classList.contains('hidden'));
        mobileToggleBtn.hidden = !isMobileView() || visibleCards.length <= 1;

        if (isMobileView() && !allCoursesExpanded && visibleCards.length > 1) {
            visibleCards.slice(1).forEach(card => card.classList.add('mobile-collapsed-hidden'));
        }

        setToggleText();
    }

    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            // Update active state
            filterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const filter = btn.dataset.filter;

            cards.forEach(card => {
                if (filter === 'all' || card.dataset.category === filter) {
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            });

            applyMobileCollapse();
        });
    });

    if (mobileToggleBtn) {
        mobileToggleBtn.addEventListener('click', () => {
            allCoursesExpanded = !allCoursesExpanded;
            applyMobileCollapse();
        });
    }

    window.addEventListener('resize', () => {
        const isMobile = isMobileView();
        if (!isMobile && wasMobile) {
            allCoursesExpanded = false;
        }
        wasMobile = isMobile;
        applyMobileCollapse();
    });

    applyMobileCollapse();
})();

(function () {
    const coursesData = [
        {
            id: 1,
            title: "Chuqur matematika",
            teacher: "Akmal Karimov",
            days: "Dushanba / Chorshanba / Juma",
            startTime: "15:00",
            endTime: "17:00",
            groups: 6,
            fullDescription: "Mazkur kurs algebra, geometriya va mantiqiy masalalarni chuqur o'rganishga yo'naltirilgan. O'quvchilar olimpiada formatidagi savollar, test strategiyalari va individual tahlil orqali yuqori natijalarga tayyorlanadi."
        },
        {
            id: 2,
            title: "Ingliz tili (IELTS)",
            teacher: "Dilnoza Rahimova",
            days: "Seshanba / Payshanba / Shanba",
            startTime: "14:00",
            endTime: "16:00",
            groups: 5,
            fullDescription: "Kurs IELTS'ning Listening, Reading, Writing va Speaking bo'limlarini kompleks yondashuvda o'rgatadi. Har hafta sinov testi, individual feedback va speaking club mashg'ulotlari o'tkaziladi."
        },
        {
            id: 3,
            title: "Kimyo va biologiya",
            teacher: "Madina Yo'ldosheva",
            days: "Dushanba / Chorshanba / Juma",
            startTime: "13:30",
            endTime: "15:30",
            groups: 4,
            fullDescription: "Tibbiyot va tabiiy fanlar yo'nalishiga tayyorlovchi ushbu kursda laboratoriya ishlari, test yechish va mavzular bo'yicha amaliy mashqlar birgalikda olib boriladi."
        },
        {
            id: 4,
            title: "Dasturlash asoslari",
            teacher: "Sardor Qodirov",
            days: "Seshanba / Payshanba / Shanba",
            startTime: "16:30",
            endTime: "18:00",
            groups: 7,
            fullDescription: "Python sintaksisi, algoritmik fikrlash va kichik loyiha yaratish jarayonlari bosqichma-bosqich o'rgatiladi. Kurs yakunida o'quvchilar real muammolarni kod orqali yecha oladi."
        },
        {
            id: 5,
            title: "Rus tili",
            teacher: "Olga Petrova",
            days: "Dushanba / Payshanba",
            startTime: "15:00",
            endTime: "16:30",
            groups: 3,
            fullDescription: "Rus tilida erkin muloqot, grammatika va matn bilan ishlash ko'nikmalarini rivojlantirishga qaratilgan kurs. Mashg'ulotlar suhbat, diktant va yozma topshiriqlar bilan boyitilgan."
        },
        {
            id: 6,
            title: "Fizika va mexanika",
            teacher: "Jahongir Sattorov",
            days: "Dushanba / Chorshanba / Shanba",
            startTime: "17:00",
            endTime: "19:00",
            groups: 4,
            fullDescription: "Kursda mexanika, elektr va optika bo'limlari olimpiada darajasidagi masalalar orqali o'rgatiladi. Nazariya va amaliy tahlil uyg'unligi o'quvchining chuqur tushunchasini shakllantiradi."
        },
        {
            id: 7,
            title: "Robototexnika",
            teacher: "Bekzod Ergashev",
            days: "Seshanba / Juma",
            startTime: "14:30",
            endTime: "16:30",
            groups: 5,
            fullDescription: "Arduino va Lego platformalari asosida sensorlar bilan ishlash, robot yig'ish va ularni dasturlash o'rgatiladi. Har oy mini-musobaqalar orqali amaliy natija mustahkamlanadi."
        },
        {
            id: 8,
            title: "Geografiya va ekologiya",
            teacher: "Nodira Islomova",
            days: "Chorshanba / Juma",
            startTime: "13:00",
            endTime: "14:30",
            groups: 3,
            fullDescription: "Dunyo geografiyasi, iqlim tizimlari va ekologik muammolarni tahlil qilishga qaratilgan kurs. Xarita bilan ishlash, keys tahlil va amaliy topshiriqlar asosiy yo'nalish hisoblanadi."
        }
    ];

    const modal = document.getElementById('course-details-modal');
    const detailsContent = document.getElementById('course-details-content');
    const detailsButtons = document.querySelectorAll('.details-btn');

    if (!modal || !detailsContent || !detailsButtons.length) return;

    function getCourseById(id) {
        return coursesData.find(course => course.id === id);
    }

    function renderDetails(course) {
        detailsContent.innerHTML = `
            <h2 id="course-details-title">${course.title}</h2>
            <ul class="course-details-meta">
                <li><strong>Ustoz:</strong> ${course.teacher}</li>
                <li><strong>Dars kunlari:</strong> ${course.days}</li>
                <li><strong>Boshlanish vaqti:</strong> ${course.startTime}</li>
                <li><strong>Tugash vaqti:</strong> ${course.endTime}</li>
                <li><strong>Guruhlar soni:</strong> ${course.groups}</li>
            </ul>
            <p>${course.fullDescription}</p>
            <a class="btn" href="register.html">Ro'yxatdan o'tish</a>
        `;
    }

    function openModalWithId(courseId, pushUrl) {
        const course = getCourseById(courseId);
        if (!course) return;

        renderDetails(course);
        modal.classList.add('active');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');

        if (pushUrl) {
            const nextUrl = `${window.location.pathname}?id=${courseId}`;
            window.history.pushState({ courseId }, '', nextUrl);
        }
    }

    function closeModal(clearUrl) {
        modal.classList.remove('active');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');

        if (clearUrl) {
            window.history.pushState({}, '', window.location.pathname);
        }
    }

    detailsButtons.forEach(btn => {
        btn.addEventListener('click', event => {
            event.preventDefault();
            const courseId = Number(btn.dataset.courseId);
            openModalWithId(courseId, true);
        });
    });

    modal.addEventListener('click', event => {
        if (event.target.closest('[data-close-modal="true"]')) {
            closeModal(true);
        }
    });

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && modal.classList.contains('active')) {
            closeModal(true);
        }
    });

    const queryId = Number(new URLSearchParams(window.location.search).get('id'));
    if (queryId) {
        openModalWithId(queryId, false);
    }
})();

(function () {
    const counters = document.querySelectorAll('.stat-num');
    let started = false;

    function animateCounters() {
        counters.forEach(counter => {
            const target = +counter.dataset.target;
            const duration = 1600; // ms
            const start = performance.now();

            function step(now) {
                const elapsed = now - start;
                const progress = Math.min(elapsed / duration, 1);
                // ease-out cubic
                const eased = 1 - Math.pow(1 - progress, 3);
                counter.textContent = Math.floor(eased * target);
                if (progress < 1) requestAnimationFrame(step);
                else counter.textContent = target;
            }

            requestAnimationFrame(step);
        });
    }

    // Trigger once the stats section enters the viewport
    const statsSection = document.querySelector('.courses-stats-section');

    if (statsSection) {
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !started) {
                    started = true;
                    animateCounters();
                    observer.disconnect();
                }
            });
        }, { threshold: 0.3 });

        observer.observe(statsSection);
    }
})();

(function () {
    const revealEls = document.querySelectorAll('.reveal');

    if (!revealEls.length) return;

    const io = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                io.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12 });

    revealEls.forEach(el => io.observe(el));
})();

})();

// ===== Source: news/new.js =====
(() => {
const navbar = document.getElementById('navbar');
const menuToggle = document.getElementById('menu-toggle');
const siteNav = document.getElementById('site-nav');
const navLinks = document.querySelectorAll('.nav-link');
const scrollTopBtn = document.getElementById('scroll-top');
const reveals = document.querySelectorAll('.reveal');
const likeButtons = document.querySelectorAll('.like-btn');
const year = document.getElementById('year');
const allNewsBtn = document.getElementById('all-news-btn');
const extraNews = document.getElementById('extra-news');

const commentModal = document.getElementById('comment-modal');
const commentClose = document.getElementById('comment-close');
const commentModalImage = document.getElementById('comment-modal-image');
const commentModalTitle = document.getElementById('comment-modal-title');
const commentModalList = document.getElementById('comment-modal-list');
const commentModalEmpty = document.getElementById('comment-modal-empty');
const commentModalForm = document.getElementById('comment-modal-form');
const commentModalInput = document.getElementById('comment-modal-input');

const newsCards = document.querySelectorAll('.news-card');
const commentsStore = {};
let activeNewsId = null;

if (year) {
  year.textContent = String(new Date().getFullYear());
}

if (menuToggle && siteNav) {
  menuToggle.addEventListener('click', () => {
    const isOpen = siteNav.classList.toggle('open');
    menuToggle.setAttribute('aria-expanded', String(isOpen));
  });

  navLinks.forEach((link) => {
    link.addEventListener('click', () => {
      siteNav.classList.remove('open');
      menuToggle.setAttribute('aria-expanded', 'false');
    });
  });
}

const onScroll = () => {
  const scrollY = window.scrollY;

  if (navbar) {
    navbar.classList.toggle('scrolled', scrollY > 30);
  }

  if (scrollTopBtn) {
    scrollTopBtn.classList.toggle('show', scrollY > 320);
  }
};

window.addEventListener('scroll', onScroll);
onScroll();

if (scrollTopBtn) {
  scrollTopBtn.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
}

if ('IntersectionObserver' in window) {
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          observer.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.18 }
  );

  reveals.forEach((item) => observer.observe(item));
} else {
  reveals.forEach((item) => item.classList.add('visible'));
}

likeButtons.forEach((button) => {
  button.addEventListener('click', () => {
    const icon = button.querySelector('i');
    const countEl = button.querySelector('.like-count');
    if (!countEl || !icon) return;

    const base = Number.parseInt(countEl.textContent || '0', 10);
    const liked = button.classList.toggle('liked');

    countEl.textContent = String(liked ? base + 1 : Math.max(0, base - 1));
    icon.classList.toggle('fa-regular', !liked);
    icon.classList.toggle('fa-solid', liked);
  });
});

const renderComments = () => {
  if (!activeNewsId || !commentModalList || !commentModalEmpty) return;

  const items = commentsStore[activeNewsId] || [];
  commentModalList.innerHTML = '';

  if (items.length === 0) {
    commentModalEmpty.style.display = 'block';
    return;
  }

  commentModalEmpty.style.display = 'none';

  items.forEach((text) => {
    const li = document.createElement('li');
    li.textContent = text;
    commentModalList.appendChild(li);
  });
};

const updateCommentCount = (newsId) => {
  const count = (commentsStore[newsId] || []).length;
  const card = document.querySelector(`.news-card[data-news-id="${newsId}"]`);
  const countEl = card?.querySelector('.comment-count');
  if (countEl) {
    countEl.textContent = String(count);
  }
};

const openCommentModal = (card, newsId) => {
  if (!commentModal) return;

  activeNewsId = newsId;

  const img = card.querySelector('img');
  const title = card.querySelector('h3');

  if (commentModalImage && img) {
    commentModalImage.src = img.src;
    commentModalImage.alt = img.alt || 'Yangilik rasmi';
  }

  if (commentModalTitle && title) {
    commentModalTitle.textContent = `${title.textContent} - Izohlar`;
  }

  renderComments();
  commentModal.classList.add('open');
  commentModal.setAttribute('aria-hidden', 'false');
  document.body.style.overflow = 'hidden';
  commentModalInput?.focus();
};

const closeCommentModal = () => {
  if (!commentModal) return;
  commentModal.classList.remove('open');
  commentModal.setAttribute('aria-hidden', 'true');
  document.body.style.overflow = '';
  activeNewsId = null;
};

newsCards.forEach((card, index) => {
  const newsId = String(index + 1);
  card.dataset.newsId = newsId;
  commentsStore[newsId] = [];

  const countEl = card.querySelector('.comment-count');
  const trigger = countEl?.parentElement;

  if (!trigger) return;

  trigger.classList.add('comment-trigger');
  trigger.setAttribute('role', 'button');
  trigger.setAttribute('tabindex', '0');

  trigger.addEventListener('click', () => openCommentModal(card, newsId));
  trigger.addEventListener('keydown', (event) => {
    if (event.key === 'Enter' || event.key === ' ') {
      event.preventDefault();
      openCommentModal(card, newsId);
    }
  });
});

if (commentModalForm && commentModalInput) {
  commentModalForm.addEventListener('submit', (event) => {
    event.preventDefault();

    if (!activeNewsId) return;

    const text = commentModalInput.value.trim();
    if (!text) return;

    commentsStore[activeNewsId].unshift(text);
    updateCommentCount(activeNewsId);
    renderComments();
    commentModalInput.value = '';
    commentModalInput.focus();
  });
}

if (commentClose) {
  commentClose.addEventListener('click', closeCommentModal);
}

if (commentModal) {
  commentModal.addEventListener('click', (event) => {
    if (event.target === commentModal) {
      closeCommentModal();
    }
  });
}

window.addEventListener('keydown', (event) => {
  if (event.key === 'Escape' && commentModal?.classList.contains('open')) {
    closeCommentModal();
  }
});

if (allNewsBtn && extraNews) {
  allNewsBtn.addEventListener('click', () => {
    const isOpen = extraNews.classList.toggle('open');
    extraNews.setAttribute('aria-hidden', String(!isOpen));
    allNewsBtn.textContent = isOpen ? 'Yopish' : 'Barcha yangiliklar';
  });
}

})();

// ===== Source: register/register.js =====
(() => {
const navbar = document.getElementById('navbar');
const menuToggle = document.getElementById('menu-toggle');
const siteNav = document.getElementById('site-nav');
const navLinks = document.querySelectorAll('.nav-link');
const registerForm = document.getElementById('register-form');
const registerMessage = document.getElementById('register-message');
const yearEl = document.getElementById('year');

if (yearEl) {
  yearEl.textContent = String(new Date().getFullYear());
}

if (menuToggle && siteNav) {
  menuToggle.addEventListener('click', () => {
    const isOpen = siteNav.classList.toggle('open');
    menuToggle.setAttribute('aria-expanded', String(isOpen));
  });
  navLinks.forEach((link) => {
    link.addEventListener('click', () => {
      siteNav.classList.remove('open');
      menuToggle.setAttribute('aria-expanded', 'false');
    });
  });
}

const onScroll = () => {
  if (navbar) {
    navbar.classList.toggle('scrolled', window.scrollY > 30);
  }
};
window.addEventListener('scroll', onScroll);
onScroll();

if (registerForm) {
  registerForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const name = document.getElementById('reg-name')?.value.trim();
    const email = document.getElementById('reg-email')?.value.trim();
    const phone = document.getElementById('reg-phone')?.value.trim();
    const password = document.getElementById('reg-password')?.value;
    const passwordConfirm = document.getElementById('reg-password-confirm')?.value;

    if (!name || !email || !phone || !password || !passwordConfirm) {
      registerMessage.textContent = "Iltimos, barcha maydonlarni to'ldiring.";
      registerMessage.style.color = '#b91c1c';
      return;
    }

    if (password.length < 6) {
      registerMessage.textContent = "Parol kamida 6 ta belgidan iborat bo'lishi kerak.";
      registerMessage.style.color = '#b91c1c';
      return;
    }

    if (password !== passwordConfirm) {
      registerMessage.textContent = "Parollar mos kelmadi.";
      registerMessage.style.color = '#b91c1c';
      return;
    }

    registerMessage.textContent = "Ro'yxatdan o'tdingiz! (Demo rejim)";
    registerMessage.style.color = '#0f766e';
    registerForm.reset();
  });
}

// Password show/hide toggle
document.querySelectorAll('.pw-toggle').forEach((btn) => {
  btn.addEventListener('click', () => {
    const targetId = btn.getAttribute('data-target');
    const input = document.getElementById(targetId);
    const icon = btn.querySelector('i');
    if (!input) return;
    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';
    icon.classList.toggle('fa-eye', !isHidden);
    icon.classList.toggle('fa-eye-slash', isHidden);
    btn.setAttribute('aria-label', isHidden ? "Parolni yashirish" : "Parolni ko'rsatish");
  });
});

})();

// ===== Source: show/sow.js =====
(() => {
const navbar = document.getElementById('navbar');
const menuToggle = document.getElementById('menu-toggle');
const siteNav = document.getElementById('site-nav');
const navLinks = document.querySelectorAll('.nav-link');
const scrollTopBtn = document.getElementById('scroll-top');
const reveals = document.querySelectorAll('.reveal');
const commentForm = document.getElementById('comment-form');
const commentLikes = document.querySelectorAll('.comment-like');
const yearEl = document.getElementById('year');

if (yearEl) {
  yearEl.textContent = String(new Date().getFullYear());
}

if (menuToggle && siteNav) {
  menuToggle.addEventListener('click', () => {
    const isOpen = siteNav.classList.toggle('open');
    menuToggle.setAttribute('aria-expanded', String(isOpen));
  });

  navLinks.forEach((link) => {
    link.addEventListener('click', () => {
      siteNav.classList.remove('open');
      menuToggle.setAttribute('aria-expanded', 'false');
    });
  });
}

const onScroll = () => {
  const scrollY = window.scrollY;

  if (navbar) {
    navbar.classList.toggle('scrolled', scrollY > 30);
  }

  if (scrollTopBtn) {
    scrollTopBtn.classList.toggle('show', scrollY > 320);
  }
};

window.addEventListener('scroll', onScroll);
onScroll();

if (scrollTopBtn) {
  scrollTopBtn.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
}

if ('IntersectionObserver' in window) {
  const revealObserver = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          revealObserver.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.16 }
  );

  reveals.forEach((item) => revealObserver.observe(item));
} else {
  reveals.forEach((item) => item.classList.add('visible'));
}

if (commentForm) {
  commentForm.addEventListener('submit', (event) => {
    event.preventDefault();

    const prev = commentForm.querySelector('.form-message');
    if (prev) prev.remove();

    const msg = document.createElement('p');
    msg.className = 'form-message';
    msg.textContent = "Izohingiz qabul qilindi. Moderator ko'rib chiqgach chop etiladi.";
    commentForm.appendChild(msg);
    commentForm.reset();

    setTimeout(() => {
      msg.remove();
    }, 3500);
  });
}

commentLikes.forEach((button) => {
  button.addEventListener('click', () => {
    const icon = button.querySelector('i');
    const countEl = button.querySelector('.like-count');
    if (!icon || !countEl) return;

    const count = Number.parseInt(countEl.textContent || '0', 10);
    const liked = button.classList.toggle('liked');

    countEl.textContent = String(liked ? count + 1 : Math.max(0, count - 1));
    icon.classList.toggle('fa-regular', !liked);
    icon.classList.toggle('fa-solid', liked);
  });
});

})();

// ===== Source: signin/signin.js =====
(() => {
const navbar = document.getElementById('navbar');
const menuToggle = document.getElementById('menu-toggle');
const siteNav = document.getElementById('site-nav');
const navLinks = document.querySelectorAll('.nav-link');
const signinForm = document.getElementById('signin-form');
const signinMessage = document.getElementById('signin-message');
const yearEl = document.getElementById('year');

if (yearEl) {
  yearEl.textContent = String(new Date().getFullYear());
}

if (menuToggle && siteNav) {
  menuToggle.addEventListener('click', () => {
    const isOpen = siteNav.classList.toggle('open');
    menuToggle.setAttribute('aria-expanded', String(isOpen));
  });
  navLinks.forEach((link) => {
    link.addEventListener('click', () => {
      siteNav.classList.remove('open');
      menuToggle.setAttribute('aria-expanded', 'false');
    });
  });
}

const onScroll = () => {
  if (navbar) {
    navbar.classList.toggle('scrolled', window.scrollY > 30);
  }
};
window.addEventListener('scroll', onScroll);
onScroll();

if (signinForm) {
  signinForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const email = document.getElementById('signin-email')?.value.trim();
    const password = document.getElementById('signin-password')?.value;

    if (!email || !password) {
      signinMessage.textContent = "Iltimos, email va parolni kiriting.";
      signinMessage.style.color = '#b91c1c';
      return;
    }

    signinMessage.textContent = "Kirish so'rovi qabul qilindi. (Demo rejim)";
    signinMessage.style.color = '#0f766e';
    signinForm.reset();
  });
}

// Password show/hide toggle
document.querySelectorAll('.pw-toggle').forEach((btn) => {
  btn.addEventListener('click', () => {
    const targetId = btn.getAttribute('data-target');
    const input = document.getElementById(targetId);
    const icon = btn.querySelector('i');
    if (!input) return;
    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';
    icon.classList.toggle('fa-eye', !isHidden);
    icon.classList.toggle('fa-eye-slash', isHidden);
    btn.setAttribute('aria-label', isHidden ? "Parolni yashirish" : "Parolni ko'rsatish");
  });
});

})();

// ===== Source: teachers/teachers.js =====
(() => {
const navbar = document.getElementById('navbar');
const menuToggle = document.getElementById('menu-toggle');
const siteNav = document.getElementById('site-nav');
const navLinks = document.querySelectorAll('.nav-link');
const scrollTopBtn = document.getElementById('scroll-top');
const reveals = document.querySelectorAll('.reveal');
const statNums = document.querySelectorAll('.stat-num');
const year = document.getElementById('year');

if (year) {
  year.textContent = String(new Date().getFullYear());
}

if (menuToggle && siteNav) {
  menuToggle.addEventListener('click', () => {
    const isOpen = siteNav.classList.toggle('open');
    menuToggle.setAttribute('aria-expanded', String(isOpen));
  });

  navLinks.forEach((link) => {
    link.addEventListener('click', () => {
      siteNav.classList.remove('open');
      menuToggle.setAttribute('aria-expanded', 'false');
    });
  });
}

const onScroll = () => {
  const scrollY = window.scrollY;

  if (navbar) {
    navbar.classList.toggle('scrolled', scrollY > 30);
  }

  if (scrollTopBtn) {
    scrollTopBtn.classList.toggle('show', scrollY > 320);
  }
};

window.addEventListener('scroll', onScroll);
onScroll();

if (scrollTopBtn) {
  scrollTopBtn.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
}

if ('IntersectionObserver' in window) {
  const revealObserver = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          revealObserver.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.18 }
  );

  reveals.forEach((item) => revealObserver.observe(item));

  const statsSection = document.querySelector('.teachers-stats-section');
  let started = false;

  if (statsSection && statNums.length) {
    const statsObserver = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting && !started) {
            started = true;

            statNums.forEach((numEl) => {
              const target = Number(numEl.dataset.target || '0');
              const duration = 1400;
              const startTime = performance.now();

              const step = (now) => {
                const progress = Math.min((now - startTime) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                numEl.textContent = String(Math.floor(eased * target));

                if (progress < 1) {
                  requestAnimationFrame(step);
                } else {
                  numEl.textContent = String(target);
                }
              };

              requestAnimationFrame(step);
            });

            statsObserver.disconnect();
          }
        });
      },
      { threshold: 0.28 }
    );

    statsObserver.observe(statsSection);
  }
} else {
  reveals.forEach((item) => item.classList.add('visible'));
}

})();

