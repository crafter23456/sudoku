      <div class="gameContainer">
        <div id="pauseOverlay">
          <svg class="resumeIconBig" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 60 60"><g fill="none" fill-rule="evenodd"><circle cx="30" cy="30" r="30" fill="#0072E3"></circle><path fill="#FFF" d="M39.12 31.98l-12.56 8.64a2.4 2.4 0 01-3.76-1.98V21.36a2.4 2.4 0 013.76-1.97l12.56 8.63a2.4 2.4 0 010 3.96z"></path></g></svg>
        </div>
        <canvas id="sudoku-canvas" width="900px" height="900px"></canvas>
      </div>
      <div id="panel">
        <div class="timerBox">
          <div class="timer" id="timer">Time: 0:00</div>
          <button id="pauseButton" onclick="pauseTimer()">
            <svg id="pauseIcon" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 320 512"><path d="M48 64C21.5 64 0 85.5 0 112V400c0 26.5 21.5 48 48 48H80c26.5 0 48-21.5 48-48V112c0-26.5-21.5-48-48-48H48zm192 0c-26.5 0-48 21.5-48 48V400c0 26.5 21.5 48 48 48h32c26.5 0 48-21.5 48-48V112c0-26.5-21.5-48-48-48H240z"/></svg>
            <svg id="resumeIcon" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 384 512"><path d="M73 39c-14.8-9.1-33.4-9.4-48.5-.9S0 62.6 0 80V432c0 17.4 9.4 33.4 24.5 41.9s33.7 8.1 48.5-.9L361 297c14.3-8.7 23-24.2 23-41s-8.7-32.2-23-41L73 39z"/></svg>
          </button>
        </div>
        <div class="control-panel">
          <button onclick="window.location.reload()">Neues Spiel</button>
          <button onclick="solveSudoku()">Lösung zeigen</button>
          <button onclick="checkIfValid()">Prüfen</button>
          <button class="number" id="clear" data-value=" ">Clear</button>
        </div>
        <div class="number-selector">
          <div class="number" data-value="1">1</div>
          <div class="number" data-value="2">2</div>
          <div class="number" data-value="3">3</div>
          <div class="number" data-value="4">4</div>
          <div class="number" data-value="5">5</div>
          <div class="number" data-value="6">6</div>
          <div class="number" data-value="7">7</div>
          <div class="number" data-value="8">8</div>
          <div class="number" data-value="9">9</div>
        </div>
      </div>
      <div id="overlay"></div>
      <div class="popup-container popup-hidden" id="popupContainer">
        <div class="popup-content">
          <p id="popup-message"></p>
          <button id="popup-close">Schließen</button>
        </div>
      </div>