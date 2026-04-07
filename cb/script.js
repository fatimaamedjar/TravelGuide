document.addEventListener('DOMContentLoaded', () => {
  const userInput = document.getElementById('user-input');
  const sendBtn = document.getElementById('send-btn');
  const chatMessages = document.getElementById('chat-messages');
  const suggestionBtns = document.querySelectorAll('.suggestion-btn');
  
  const localKnowledge = {
    "tarifs": "Nos hôtels commencent à partir de 50€ la nuit. Les riads traditionnels coûtent entre 70€ et 150€ selon le standing.",
    "disponibilité": "Veuillez indiquer vos dates de séjour pour que je vérifie la disponibilité en temps réel.",
    "occupation": "Simple, Double, Familiale et Suites disponibles selon les hôtels (jusqu'à 6 personnes).",
    "piscine": "La plupart de nos hôtels ont une piscine, et plusieurs riads offrent des espaces de bienêtre.",
    "réservation": "Vous pouvez réserver immédiatement par carte. Annulation gratuite jusqu'à 48h avant.",
    "réserver" : "Pour réserver un séjour au Maroc, précisez : 1. Type (hôtel, riad) 2. Destination (ex: Marrakech, agadir, Essaouira) 3. Dates et nombre de voyageurs. Je vous propose dès que vous avez ces détails !",
    //"marrakech": "À Marrakech, nous proposons des hébergements près de la Médina, de la Palmeraie et de Guéliz.",
    //"agadir": "À Agadir, nos établissements sont pour la plupart en bord de mer avec vue sur l'océan.",
    //"fès": "À Fès, nous avons des riads traditionnels de charme au cœur de la médina classée UNESCO.",
    "enfants": "Oui, la plupart de nos établissements acceptent les enfants à partir de 2 ans.",
    "petit-déjeuner": "Le petit-déjeuner marocain traditionnel est généralement inclus. Petit-déjeuner continental disponible avec supplément.",
    "excursion": "Nous proposons des excursions au désert, dans les montagnes de l'Atlas, aux cascades d'Ouzoud et dans les villes impériales.",
    "merci": "Merci à vous ! Pour toute demande d'hôtel, riad, séjour ou activité au Maroc , n'hésitez pas.",
    "au revoir": "À Bientôt!"
  };
  
  const greetings = [
    "Bonjour ! Comment puis-je vous aider pour votre séjour au Maroc ?",
    "Bienvenue ! Je suis à votre disposition pour toute question sur les hôtels et activités au Maroc.",
    "Salutations ! Dites-moi ce que vous recherchez et je trouverai la meilleure offre pour vous."
  ];
  
  const context_str = "Tu es un assistant de site web TravelGuide. ";
  const context_str2 = "Extensions valides de service: Hôtels, Riads, Séjour, Circuit tourism, désert, Atlas, Ouarzazate, Essaouira, Marrakech, Casablanca.";
  const context_str3 = "Limites: Ne répond qu'aux demandes concernant les réservations au Maroc. Si la requête est hors sujet le rappeler poliment.";
  const context_str4 = "Fournir des informations depuis la base locale si disponible. Sinon, faire des suggestions créatives mais crédibles.";
  const context_str5 = "Les réponses fournit doivent être courte et compréhensible. Les réponses ne doivent pas dépasser 40 mots.";
  const context_str6 = "Ne pas suggérer des offres qui n'existe pas dans la base local.";
  const basePrompt = context_str + context_str2 + context_str3 + context_str4 + context_str5 + context_str6;

  function addUserMessage(message) {
    const messageElement = document.createElement('div');
    messageElement.classList.add('message', 'user-message');
    messageElement.textContent = message;
    chatMessages.appendChild(messageElement);
    
    chatMessages.scrollTop = chatMessages.scrollHeight;
  }
  
  function addBotMessage(message, fromDb = false) {
    const messageElement = document.createElement('div');
    messageElement.classList.add('message', 'bot-message');
    messageElement.innerHTML = message;
    if(!fromDb) {
      messageElement.innerHTML += `<div class="ai-indicator">deepseek-r1-0528 • OpenRouter AI</div>`;
    }
    chatMessages.appendChild(messageElement);
    
    chatMessages.scrollTop = chatMessages.scrollHeight;
  }
  
  function showTyping() {
    const indicator = document.createElement('div');
    indicator.classList.add('message', 'bot-message', 'typing-indicator', 'active');
    indicator.id = "typing-indicator";
    
    for(let i=0; i<3; i++) {
      const dot = document.createElement('div');
      dot.classList.add('typing-dot');
      indicator.appendChild(dot);
    }
    
    chatMessages.appendChild(indicator);
    chatMessages.scrollTop = chatMessages.scrollHeight;
  }
  
  function hideTyping() {
    const indicator = document.getElementById('typing-indicator');
    if(indicator) {
      indicator.remove();
    }
  }
  
  function searchInLocalDb(question) {
    question = question.toLowerCase();
    
    for(const key in localKnowledge) {
      if(question.includes(key)) {
        return localKnowledge[key];
      }
    }
    
    return null;
  }
  
  async function queryOpenRouterAPI(question) {
    const API_URL = 'https://openrouter.ai/api/v1/chat/completions';
    const API_KEY = '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';
    
    showTyping();
    
    try {
      const response = await fetch(API_URL, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${API_KEY}`
        },
        body: JSON.stringify({
          "model": "deepseek/deepseek-r1-0528:free",
          "messages": [
            {"role": "system", "content": basePrompt},
            {"role": "user", "content": question}
          ]
        })
      });
      
      const data = await response.json();
      hideTyping();
      
      if(data.choices && data.choices.length > 0) {
        return data.choices[0].message.content;
      } else {
        return "Désolé, je n'ai pas pu obtenir de réponse. Pouvez-vous reformuler s'il vous plaît ?";
      }
    } catch (error) {
      hideTyping();
      return "Erreur de connexion à l'API. Veuillez réessayer.";
    }
  }
  
  async function processMessage(question) {
    const dbResponse = searchInLocalDb(question);
    if(dbResponse) {
      setTimeout(() => {
        addBotMessage(dbResponse, true);
      }, 800);
      return;
    }
    
    const apiResponse = await queryOpenRouterAPI(question);
    addBotMessage(apiResponse);
  }
  
  function handleSendMessage() {
    const message = userInput.value.trim();
    if(message) {
      addUserMessage(message);
      userInput.value = '';
      
      // Gérer les salutations par défaut
      if(message.toLowerCase().includes('bonjour') || message.toLowerCase().includes('salut')) {
        setTimeout(() => {
          addBotMessage(greetings[Math.floor(Math.random() * greetings.length)], true);
        }, 800);
      } else {
        processMessage(message);
      }
    }
  }
  
  sendBtn.addEventListener('click', handleSendMessage);
  
  userInput.addEventListener('keypress', (e) => {
    if(e.key === 'Enter') {
      handleSendMessage();
    }
  });
  
  suggestionBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      userInput.value = btn.textContent;
      handleSendMessage();
    });
  });
});