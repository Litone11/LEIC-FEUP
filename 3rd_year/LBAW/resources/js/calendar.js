import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import axios from 'axios';

export function setupCalendar() {
  const calendarEl = document.getElementById('calendar');
  if (!calendarEl) return;

  const calendar = new Calendar(calendarEl, {
    plugins: [dayGridPlugin, timeGridPlugin, listPlugin],
    initialView: 'dayGridMonth',
    height: '100%',
    expandRows: true,

    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth,dayGridWeek,listWeek',
    },

    events: (info, successCallback, failureCallback) => {
      const selectedProjects = Array.from(
        document.querySelectorAll('[data-project-id]:checked')
      ).map(cb => cb.dataset.projectId);

      if (selectedProjects.length === 0) {
        successCallback([]);
        return;
      }

      axios
        .get('/calendar/events', {
          params: { projects: selectedProjects },
        })
        .then(res => successCallback(res.data))
        .catch(err => {
          console.error('Calendar events load failed', err);
          failureCallback(err);
        });
    },
  });

  calendar.render();

  const firstProject = document.querySelector('[data-project-id]');
  if (firstProject) {
    firstProject.checked = true;
    calendar.refetchEvents();
  }
    // ---------------------------
    // Project carousel 
    // ---------------------------
    const items = Array.from(document.querySelectorAll('.project-item'));
    const prevBtn = document.getElementById('projects-prev');
    const nextBtn = document.getElementById('projects-next');

    let startIndex = 0;
    const visibleCount = 3;

    function renderCarousel() {
    items.forEach((el, i) => {
        if (i >= startIndex && i < startIndex + visibleCount) {
        el.classList.remove('hidden');
        } else {
        el.classList.add('hidden');
        }
    });

    if (prevBtn) prevBtn.disabled = startIndex === 0;
    if (nextBtn) nextBtn.disabled = startIndex + visibleCount >= items.length;
    }

    renderCarousel();

    prevBtn?.addEventListener('click', () => {
    if (startIndex > 0) {
        startIndex -= 1;
        renderCarousel();
    }
    });

    nextBtn?.addEventListener('click', () => {
    if (startIndex + visibleCount < items.length) {
        startIndex += 1;
        renderCarousel();
    }
    });

  document
    .querySelectorAll('[data-project-id]')
    .forEach(cb => {
      cb.addEventListener('change', () => {
        calendar.refetchEvents();
      });
    });

}
