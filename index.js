$(document).ready(function(){
  // Initialisation du carousel de la bannière
  $('.home .owl-carousel').owlCarousel({
    loop: true,
    margin: 0,
    autoplay: true,
    autoplayTimeout: 3000,
    autoplayHoverPause: false,
    navText: ["<i class = 'fa fa-chevron-left'></i>", "<i class = 'fa fa-chevron-right'></i>"],
    responsive: {
      0: {
        items: 1
      },
      768: {
        items: 1
      },
      1000: {
        items: 1
      }
    }
  });

  // Initialisation du carousel de la galerie
  $('.gallery .owl-carousel1').owlCarousel({
    loop: true,
    margin: 0,
    nav: false,
    dots: false,
    autoplay: true,
    autoplayTimeout: 1000,
    autoplayHoverPause: true,
    navText: ["<i class = 'fa fa-chevron-left'></i>", "<i class = 'fa fa-chevron-right'></i>"],
    responsive: {
      0: {
        items: 1
      },
      768: {
        items: 4,
      },
      1000: {
        items: 6
      }
    }
  });

  // Initialisation du carousel des services
  $('.services .owl-carousel').owlCarousel({
    loop: true,
    margin: 20, 
    nav: true, 
    dots: false,
    autoplay: false,
    
    autoplayHoverPause: false,
    navText: [
      "<", 
      ">"
    ],
    responsive: {
      0: {
        items: 1 
      },
      768: {
        items: 2 
      },
      1000: {
        items: 3 
      }
    }
  });

  // Initialisation du carousel des hôtels
  $('.rooms .owl-carousel').owlCarousel({
    loop: true,
    margin: 20, 
    nav: true, 
    dots: false,
    autoplay: true,
    autoplayTimeout: 2000,
    autoplayHoverPause: true,
    navText: [
      "<", 
      ">"
    ],
    responsive: {
      0: {
        items: 1 
      },
      768: {
        items: 2 
      },
      1000: {
        items: 3 
      }
    }
  });
});

// Fonctions de navigation pour le carousel des services
function prevServices() {
  $('.services .owl-carousel').trigger('prev.owl.carousel');
}
function nextServices() {
  $('.services .owl-carousel').trigger('next.owl.carousel');
}

// Fonctions de navigation pour le carousel des hôtels
function prevHotels() {
  $('.rooms .owl-carousel').trigger('prev.owl.carousel');
}
function nextHotels() {
  $('.rooms .owl-carousel').trigger('next.owl.carousel');
}

//pour le calendrier 
flatpickr(".datepicker", {
  locale: "fr"
});
const departInput = document.getElementById('date-depart');
const arriveeInput = document.getElementById('date-arrivee');

const fpDepart = flatpickr(departInput, {
  dateFormat: "d/m/Y",
  onChange: function(selectedDates, dateStr, instance) {
    if (selectedDates.length) {
      fpArrivee.set('minDate', selectedDates[0]);
      if (fpArrivee.selectedDates.length && fpArrivee.selectedDates[0] < selectedDates[0]) {
        fpArrivee.setDate(selectedDates[0], true);
      }
    }
  }
});

const fpArrivee = flatpickr(arriveeInput, {
  dateFormat: "d/m/Y",
  onChange: function(selectedDates, dateStr, instance) {
    if (selectedDates.length) {
      fpDepart.set('maxDate', selectedDates[0]);
      if (fpDepart.selectedDates.length && fpDepart.selectedDates[0] > selectedDates[0]) {
        fpDepart.setDate(selectedDates[0], true);
      }
    }
  }
});

//pour le champs des voyaageurs
let rooms = [
  { adults: 2, children: 0, childrenAges: [] }
];

// Affiche ou masque le popup
function toggleTravelerPopup() {
  const popup = document.getElementById('traveler-popup');
  popup.style.display = (popup.style.display === 'block') ? 'none' : 'block';
  renderRooms();
}

// Ferme le popup (utilisé par le bouton "Fermer")
function closeTravelerPopup() {
  document.getElementById('traveler-popup').style.display = 'none';
}

// Ajoute une chambre
function addRoom() {
  rooms.push({ adults: 1, children: 0, childrenAges: [] });
  renderRooms();
  updateSummary();
}

// Retire une chambre
function removeRoom(index) {
  if (rooms.length > 1) {
    rooms.splice(index, 1);
    renderRooms();
    updateSummary();
  }
}

// Change le nombre d'adultes ou d'enfants
function changeCount(roomIdx, type, delta) {
  let room = rooms[roomIdx];
  if (type === 'adults') {
    room.adults = Math.max(1, room.adults + delta);
  } else if (type === 'children') {
    room.children = Math.max(0, room.children + delta);
    if (delta > 0) {
      room.childrenAges.push(0);
    } else if (delta < 0 && room.childrenAges.length > 0) {
      room.childrenAges.pop();
    }
  }
  renderRooms();
  updateSummary();
}

// Change l'âge d'un enfant
function setChildAge(roomIdx, childIdx, value) {
  rooms[roomIdx].childrenAges[childIdx] = parseInt(value, 10);
  updateSummary();
}

// Affiche dynamiquement les chambres et les compteurs
function renderRooms() {
  const container = document.getElementById('rooms-container');
  container.innerHTML = '';
  rooms.forEach((room, idx) => {
    const roomDiv = document.createElement('div');
    roomDiv.className = 'room-block';
    roomDiv.innerHTML = `
      <div class="room-title">Chambre ${idx + 1}</div>
      <div class="counter-row">
        <span class="counter-label">Adultes</span>
        <button type="button" class="counter-btn" onclick="changeCount(${idx}, 'adults', -1)">–</button>
        <input class="counter-value" type="text" value="${room.adults}" readonly>
        <button type="button" class="counter-btn" onclick="changeCount(${idx}, 'adults', 1)">+</button>
      </div>
      <div class="counter-row">
        <span class="counter-label">Enfants<br><span style="font-size:11px;color:#888;">0 à 17 ans</span></span>
        <button type="button" class="counter-btn" onclick="changeCount(${idx}, 'children', -1)">–</button>
        <input class="counter-value" type="text" value="${room.children}" readonly>
        <button type="button" class="counter-btn" onclick="changeCount(${idx}, 'children', 1)">+</button>
      </div>
      ${room.childrenAges.map((age, cidx) => `
        <div class="child-age-row">
          <label>Âge de l'enfant ${cidx + 1} :</label>
          <select onchange="setChildAge(${idx}, ${cidx}, this.value)">
            ${Array.from({length: 18}, (_, i) => `<option value="${i}" ${age==i?'selected':''}>${i}</option>`).join('')}
          </select>
        </div>
      `).join('')}
      ${rooms.length > 1 ? `<button type="button" class="remove-room-btn" onclick="removeRoom(${idx})">Supprimer la chambre</button>` : ''}
    `;
    container.appendChild(roomDiv);
  });
}

// Met à jour le résumé
function updateSummary() {
  let totalAdults = rooms.reduce((sum, r) => sum + r.adults, 0);
  let totalChildren = rooms.reduce((sum, r) => sum + r.children, 0);
  let totalTravelers = totalAdults + totalChildren;
  let summary = `${totalTravelers} voyageur${totalTravelers>1?'s':''}, ${rooms.length} chambre${rooms.length>1?'s':''}`;
  document.getElementById('traveler-summary-text').textContent = summary;
}

// Ferme le popup si on clique en dehors
document.addEventListener('mousedown', function(event) {
  const selector = document.querySelector('.traveler-selector');
  const popup = document.getElementById('traveler-popup');
  if (popup.style.display === 'block' && !selector.contains(event.target)) {
    popup.style.display = 'none';
  }
});

// Initialisation au chargement
document.addEventListener('DOMContentLoaded', function() {
  renderRooms();
  updateSummary();
});


