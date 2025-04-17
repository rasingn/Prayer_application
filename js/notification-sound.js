// Custom sound player for notifications
// This script will play custom sounds when notifications are received

// Audio context for playing sounds
let audioContext = null;

// Initialize audio context (must be done after user interaction)
function initAudioContext() {
  if (audioContext === null) {
    try {
      // Create new audio context
      audioContext = new (window.AudioContext || window.webkitAudioContext)();
      console.log('Audio context initialized successfully');
      return true;
    } catch (error) {
      console.error('Failed to initialize audio context:', error);
      return false;
    }
  }
  return true;
}

// Load and cache sound files
const soundCache = {};

// Load a sound file
async function loadSound(url) {
  if (soundCache[url]) {
    return soundCache[url];
  }
  
  try {
    const response = await fetch(url);
    const arrayBuffer = await response.arrayBuffer();
    const audioBuffer = await audioContext.decodeAudioData(arrayBuffer);
    soundCache[url] = audioBuffer;
    return audioBuffer;
  } catch (error) {
    console.error('Error loading sound:', error);
    return null;
  }
}

// Play a sound
function playSound(buffer) {
  if (!audioContext || !buffer) {
    return false;
  }
  
  try {
    const source = audioContext.createBufferSource();
    source.buffer = buffer;
    source.connect(audioContext.destination);
    source.start(0);
    return true;
  } catch (error) {
    console.error('Error playing sound:', error);
    return false;
  }
}

// Play notification sound
async function playNotificationSound(soundUrl = '/assets/notification-sound.mp3') {
  // Check if sound is enabled
  const soundEnabled = localStorage.getItem('soundEnabled') === 'true';
  if (!soundEnabled) {
    console.log('Sound is disabled');
    return false;
  }
  
  // Initialize audio context if needed
  if (!initAudioContext()) {
    return false;
  }
  
  // Load and play sound
  const soundBuffer = await loadSound(soundUrl);
  if (soundBuffer) {
    return playSound(soundBuffer);
  }
  
  return false;
}

// Listen for messages from service worker
navigator.serviceWorker.addEventListener('message', async (event) => {
  if (event.data && event.data.type === 'PUSH_RECEIVED') {
    console.log('Push notification received, playing sound');
    await playNotificationSound();
  }
});

// Initialize sound system on page load
document.addEventListener('DOMContentLoaded', () => {
  // Set default sound setting if not set
  if (localStorage.getItem('soundEnabled') === null) {
    localStorage.setItem('soundEnabled', 'true');
  }
  
  // Initialize audio context on first user interaction
  document.addEventListener('click', () => {
    initAudioContext();
  }, { once: true });
});

// Export functions for use in other scripts
window.notificationSound = {
  play: playNotificationSound,
  init: initAudioContext
};
