(() => {
  const previewRoot = document.querySelector('[data-course-teacher-preview]');
  if (!previewRoot) return;

  const previewData = JSON.parse(previewRoot.dataset.coursePreview || '[]');
  const previewMap = new Map(previewData.map((teacher) => [String(teacher.id), teacher]));
  const select = document.querySelector('[data-course-teacher-select]');
  const image = previewRoot.querySelector('[data-preview-image]');
  const name = previewRoot.querySelector('[data-preview-name]');
  const subject = previewRoot.querySelector('[data-preview-subject]');
  const experience = previewRoot.querySelector('[data-preview-experience]');
  const grades = previewRoot.querySelector('[data-preview-grades]');
  const bio = previewRoot.querySelector('[data-preview-bio]');
  const achievements = previewRoot.querySelector('[data-preview-achievements]');
  const fallbackImage = previewRoot.dataset.coursePreviewFallback || '';
  const initialTeacherId = previewRoot.dataset.courseInitialTeacherId || '';

  function renderAchievements(items) {
    if (!achievements) return;

    achievements.innerHTML = '';
    if (!items.length) {
      const item = document.createElement('li');
      item.className = 'course-create-placeholder';
      item.textContent = "Ustoz tanlanganda yoki profil bog'langanda yutuqlar shu yerda chiqadi.";
      achievements.appendChild(item);
      return;
    }

    items.forEach((text) => {
      const item = document.createElement('li');
      const icon = document.createElement('i');
      icon.className = 'fa-solid fa-award';
      item.appendChild(icon);
      item.appendChild(document.createTextNode(` ${text}`));
      achievements.appendChild(item);
    });
  }

  function renderTeacher(teacher) {
    if (image) image.src = teacher?.image || fallbackImage;
    if (name) name.textContent = teacher?.name || 'Ustoz tanlanmagan';
    if (subject) subject.textContent = teacher?.subject || "Avval ustozni tanlang";
    if (experience) experience.textContent = teacher?.experience_label || '-';
    if (grades) grades.textContent = teacher?.grades || '-';
    if (bio) {
      bio.textContent = teacher?.bio || "Tanlangan ustozning qisqa ma'lumoti shu yerda ko'rinadi.";
    }

    renderAchievements(teacher?.achievements || []);
  }

  if (select) {
    renderTeacher(previewMap.get(String(select.value)) || null);
    select.addEventListener('change', () => {
      renderTeacher(previewMap.get(String(select.value)) || null);
    });
  } else {
    renderTeacher(previewMap.get(String(initialTeacherId)) || null);
  }
})();
