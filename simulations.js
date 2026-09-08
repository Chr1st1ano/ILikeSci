/**
 * ILikeSci — Native Offline Interactive Science Simulation Labs
 * High-performance 60fps HTML5 Canvas laboratory simulations
 * Optimized for low-end hardware, school tablets, and smart TVs
 */

const SciSimulations = (function () {
  let activeAnimationId = null;
  let activeLabId = null;
  let currentLabState = {};

  function stopCurrentSimulation() {
    if (activeAnimationId) {
      cancelAnimationFrame(activeAnimationId);
      activeAnimationId = null;
    }
  }

  // Helper: Play sound chime if app.js is available
  function playSound(type) {
    if (window.app && typeof window.app.playChime === 'function') {
      window.app.playChime(type);
    }
  }

  // --- LAB 1: Photosynthesis & Plant Growth Simulator ---
  function initPhotosynthesisLab(canvas, controlsContainer, challengeContainer) {
    stopCurrentSimulation();
    const ctx = canvas.getContext('2d');

    const state = {
      sunlight: 80, // 0 - 100
      water: 75,    // 0 - 100
      co2: 60,      // 0 - 100
      growthStage: 0.4, // 0 (seed) to 1.0 (mature blooming plant)
      time: 0,
      bubbles: [],
      co2Particles: []
    };
    currentLabState = state;

    // Controls UI
    controlsContainer.innerHTML = `
      <div class="sim-control-group">
        <label><i class="fa-solid fa-sun text-warning"></i> Sunlight Intensity: <span id="val-sun" class="sim-val">${state.sunlight}%</span></label>
        <input type="range" id="slider-sun" min="0" max="100" value="${state.sunlight}" class="form-control sim-slider">
      </div>
      <div class="sim-control-group">
        <label><i class="fa-solid fa-droplet text-primary"></i> Soil Moisture / Water: <span id="val-water" class="sim-val">${state.water}%</span></label>
        <input type="range" id="slider-water" min="0" max="100" value="${state.water}" class="form-control sim-slider">
      </div>
      <div class="sim-control-group">
        <label><i class="fa-solid fa-wind" style="color:#06d6a0;"></i> Carbon Dioxide ($CO_2$): <span id="val-co2" class="sim-val">${state.co2}%</span></label>
        <input type="range" id="slider-co2" min="0" max="100" value="${state.co2}" class="form-control sim-slider">
      </div>
      <div class="sim-stat-card">
        <div class="sim-metric">
          <span class="metric-label">Photosynthesis Rate</span>
          <div class="sim-progress-track"><div id="rate-bar" class="sim-progress-fill bg-success" style="width:70%;"></div></div>
          <span id="rate-text" class="metric-val text-success">72% Optimal</span>
        </div>
        <div class="sim-metric mt-10">
          <span class="metric-label">Oxygen ($O_2$) Emission</span>
          <span id="o2-text" class="metric-val text-primary">Moderate (65 ppm)</span>
        </div>
        <div class="sim-metric mt-10">
          <span class="metric-label">Plant Stage</span>
          <span id="stage-text" class="metric-val text-warning">Vegetative Growth</span>
        </div>
      </div>
      <div style="display:flex; gap:8px; margin-top:14px;">
        <button class="btn btn-secondary btn-sm flex-1" id="btn-reset-plant"><i class="fa-solid fa-rotate-left"></i> Reset Seed</button>
        <button class="btn btn-primary btn-sm flex-1" id="btn-boost-plant"><i class="fa-solid fa-wand-magic-sparkles"></i> Optimal Balance</button>
      </div>
    `;

    challengeContainer.innerHTML = `
      <div class="sim-challenge-card">
        <h4><i class="fa-solid fa-clipboard-question text-primary"></i> Oral Recitation Challenge</h4>
        <p><strong>Question:</strong> What happens to the plant's photosynthesis rate and growth if sunlight is at 100% but water drops to 0%?</p>
        <div class="sim-answer-hint">
          <small>💡 <em>Hint: Water is a raw material required for the light-dependent reactions of photosynthesis ($6CO_2 + 6H_2O \rightarrow C_6H_{12}O_6 + 6O_2$).</em></small>
        </div>
      </div>
    `;

    // Bind slider events
    const sunSlider = document.getElementById('slider-sun');
    const waterSlider = document.getElementById('slider-water');
    const co2Slider = document.getElementById('slider-co2');

    sunSlider.oninput = (e) => {
      state.sunlight = parseInt(e.target.value);
      document.getElementById('val-sun').textContent = `${state.sunlight}%`;
      playSound('tick');
    };
    waterSlider.oninput = (e) => {
      state.water = parseInt(e.target.value);
      document.getElementById('val-water').textContent = `${state.water}%`;
      playSound('tick');
    };
    co2Slider.oninput = (e) => {
      state.co2 = parseInt(e.target.value);
      document.getElementById('val-co2').textContent = `${state.co2}%`;
      playSound('tick');
    };

    document.getElementById('btn-reset-plant').onclick = () => {
      state.growthStage = 0.05;
      state.sunlight = 50;
      state.water = 50;
      state.co2 = 50;
      sunSlider.value = 50; waterSlider.value = 50; co2Slider.value = 50;
      document.getElementById('val-sun').textContent = '50%';
      document.getElementById('val-water').textContent = '50%';
      document.getElementById('val-co2').textContent = '50%';
      playSound('click');
    };

    document.getElementById('btn-boost-plant').onclick = () => {
      state.sunlight = 85;
      state.water = 80;
      state.co2 = 75;
      sunSlider.value = 85; waterSlider.value = 80; co2Slider.value = 75;
      document.getElementById('val-sun').textContent = '85%';
      document.getElementById('val-water').textContent = '80%';
      document.getElementById('val-co2').textContent = '75%';
      playSound('coin');
    };

    // Animation Loop
    function render() {
      state.time += 0.02;
      const w = canvas.width;
      const h = canvas.height;

      // Calculate efficiency: limiting factor principle (Liebig's Law)
      const minFactor = Math.min(state.sunlight, state.water, state.co2);
      const photoRate = (state.sunlight * 0.4 + state.water * 0.35 + state.co2 * 0.25) * (minFactor / 100);
      
      // Gradually adjust growth stage
      if (minFactor > 15) {
        state.growthStage = Math.min(1.0, state.growthStage + (photoRate / 100) * 0.001);
      } else {
        state.growthStage = Math.max(0.05, state.growthStage - 0.0003); // wilting
      }

      // Update HUD metrics
      const rateBar = document.getElementById('rate-bar');
      const rateText = document.getElementById('rate-text');
      const o2Text = document.getElementById('o2-text');
      const stageText = document.getElementById('stage-text');

      if (rateBar && rateText) {
        rateBar.style.width = `${Math.min(100, Math.round(photoRate))}%`;
        rateText.textContent = `${Math.round(photoRate)}% Optimal`;
        if (photoRate < 30) {
          rateBar.className = 'sim-progress-fill bg-danger';
          rateText.className = 'metric-val text-danger';
        } else if (photoRate < 65) {
          rateBar.className = 'sim-progress-fill bg-warning';
          rateText.className = 'metric-val text-warning';
        } else {
          rateBar.className = 'sim-progress-fill bg-success';
          rateText.className = 'metric-val text-success';
        }
      }

      if (o2Text) {
        const o2Ppm = Math.round(photoRate * 1.2);
        o2Text.textContent = `${o2Ppm} ppm (${photoRate > 60 ? 'High' : photoRate > 25 ? 'Moderate' : 'Low'})`;
      }

      if (stageText) {
        let sName = 'Seedling';
        if (state.growthStage < 0.2) sName = 'Germination (Sprout)';
        else if (state.growthStage < 0.5) sName = 'Early Vegetative';
        else if (state.growthStage < 0.8) sName = 'Mature Foliage';
        else sName = 'Full Bloom & Flowering 🌸';
        stageText.textContent = sName;
      }

      // 1. Sky & Sun background
      const skyBrightness = Math.max(0.1, state.sunlight / 100);
      const skyGrad = ctx.createLinearGradient(0, 0, 0, h * 0.7);
      skyGrad.addColorStop(0, `rgba(56, 189, 248, ${skyBrightness})`);
      skyGrad.addColorStop(1, `rgba(224, 242, 254, ${skyBrightness})`);
      ctx.fillStyle = skyGrad;
      ctx.fillRect(0, 0, w, h * 0.7);

      // Sun
      const sunX = w * 0.85;
      const sunY = h * 0.18;
      const sunRadius = 28 + (state.sunlight / 100) * 16;
      ctx.beginPath();
      ctx.arc(sunX, sunY, sunRadius, 0, Math.PI * 2);
      ctx.fillStyle = `rgba(251, 191, 36, ${Math.max(0.3, state.sunlight / 100)})`;
      ctx.shadowColor = '#f59e0b';
      ctx.shadowBlur = (state.sunlight / 100) * 35;
      ctx.fill();
      ctx.shadowBlur = 0;

      // Sun Rays
      if (state.sunlight > 20) {
        ctx.strokeStyle = `rgba(253, 230, 138, ${(state.sunlight / 100) * 0.35})`;
        ctx.lineWidth = 2;
        for (let i = 0; i < 8; i++) {
          const angle = (state.time * 0.2) + (i * Math.PI) / 4;
          ctx.beginPath();
          ctx.moveTo(sunX + Math.cos(angle) * (sunRadius + 6), sunY + Math.sin(angle) * (sunRadius + 6));
          ctx.lineTo(sunX + Math.cos(angle) * (sunRadius + 22), sunY + Math.sin(angle) * (sunRadius + 22));
          ctx.stroke();
        }
      }

      // 2. Ground & Soil
      const groundY = h * 0.7;
      const soilMoisture = state.water / 100;
      const soilGrad = ctx.createLinearGradient(0, groundY, 0, h);
      soilGrad.addColorStop(0, `rgb(${Math.round(120 - soilMoisture * 40)}, ${Math.round(80 - soilMoisture * 30)}, 50)`);
      soilGrad.addColorStop(1, `rgb(${Math.round(80 - soilMoisture * 30)}, ${Math.round(50 - soilMoisture * 20)}, 30)`);
      ctx.fillStyle = soilGrad;
      ctx.fillRect(0, groundY, w, h - groundY);

      // Grass top layer
      ctx.fillStyle = state.water > 20 ? '#10b981' : '#b45309';
      ctx.fillRect(0, groundY - 4, w, 8);

      // 3. Roots System (underground)
      const plantRootX = w * 0.45;
      ctx.strokeStyle = '#d97706';
      ctx.lineWidth = 3;
      ctx.beginPath();
      ctx.moveTo(plantRootX, groundY);
      const rootDepth = 20 + state.growthStage * 60;
      ctx.lineTo(plantRootX - 15, groundY + rootDepth * 0.7);
      ctx.moveTo(plantRootX, groundY);
      ctx.lineTo(plantRootX + 18, groundY + rootDepth * 0.85);
      ctx.moveTo(plantRootX, groundY + 15);
      ctx.lineTo(plantRootX, groundY + rootDepth);
      ctx.stroke();

      // 4. Plant Stem & Foliage
      const plantHeight = 40 + state.growthStage * (h * 0.45);
      const stemTopY = groundY - plantHeight;
      const sway = Math.sin(state.time * 2) * (2 + state.growthStage * 3);

      // Stem
      ctx.strokeStyle = state.water < 20 ? '#a16207' : '#059669';
      ctx.lineWidth = 5 + state.growthStage * 4;
      ctx.lineCap = 'round';
      ctx.beginPath();
      ctx.moveTo(plantRootX, groundY);
      ctx.quadraticCurveTo(plantRootX + sway, groundY - plantHeight * 0.5, plantRootX + sway * 1.5, stemTopY);
      ctx.stroke();

      // Leaves
      const leafCount = Math.floor(2 + state.growthStage * 8);
      const leafColor = state.water < 20 ? '#ca8a04' : '#10b981';
      for (let i = 0; i < leafCount; i++) {
        const leafProgress = (i + 1) / (leafCount + 1);
        const leafY = groundY - plantHeight * leafProgress;
        const isRight = i % 2 === 0;
        const leafLength = 16 + state.growthStage * 22;
        const leafAngle = (isRight ? 0.35 : -0.35) + Math.sin(state.time * 1.5 + i) * 0.05;

        ctx.save();
        ctx.translate(plantRootX + sway * leafProgress, leafY);
        ctx.rotate(leafAngle);
        ctx.fillStyle = leafColor;
        ctx.beginPath();
        ctx.ellipse(isRight ? leafLength * 0.6 : -leafLength * 0.6, 0, leafLength * 0.6, 8 + state.growthStage * 3, 0, 0, Math.PI * 2);
        ctx.fill();
        ctx.restore();
      }

      // Flower on top if mature
      if (state.growthStage > 0.75) {
        const flowerX = plantRootX + sway * 1.5;
        const flowerY = stemTopY;
        const petalCount = 6;
        for (let p = 0; p < petalCount; p++) {
          const petalAngle = (p * Math.PI * 2) / petalCount + state.time * 0.3;
          ctx.beginPath();
          ctx.arc(flowerX + Math.cos(petalAngle) * 14, flowerY + Math.sin(petalAngle) * 14, 9, 0, Math.PI * 2);
          ctx.fillStyle = '#ec4899';
          ctx.fill();
        }
        ctx.beginPath();
        ctx.arc(flowerX, flowerY, 10, 0, Math.PI * 2);
        ctx.fillStyle = '#fbbf24';
        ctx.fill();
      }

      // 5. Oxygen ($O_2$) Bubbles Rising
      if (photoRate > 15 && Math.random() < 0.3) {
        state.bubbles.push({
          x: plantRootX + (Math.random() - 0.5) * 60,
          y: groundY - Math.random() * plantHeight,
          radius: 2 + Math.random() * 4,
          speed: 1 + Math.random() * 1.5,
          opacity: 0.9
        });
      }

      for (let b = state.bubbles.length - 1; b >= 0; b--) {
        const bubble = state.bubbles[b];
        bubble.y -= bubble.speed;
        bubble.x += Math.sin(state.time * 3 + b) * 0.5;
        bubble.opacity -= 0.008;

        ctx.beginPath();
        ctx.arc(bubble.x, bubble.y, bubble.radius, 0, Math.PI * 2);
        ctx.fillStyle = `rgba(56, 189, 248, ${bubble.opacity})`;
        ctx.fill();

        ctx.fillStyle = `rgba(255, 255, 255, ${bubble.opacity})`;
        ctx.font = 'bold 9px sans-serif';
        ctx.fillText('O₂', bubble.x + 5, bubble.y + 3);

        if (bubble.opacity <= 0 || bubble.y < 20) {
          state.bubbles.splice(b, 1);
        }
      }

      // 6. Chemical Formula Overlay Banner
      ctx.fillStyle = 'rgba(15, 23, 42, 0.75)';
      ctx.fillRect(16, 16, 260, 48);
      ctx.strokeStyle = 'rgba(255, 255, 255, 0.2)';
      ctx.lineWidth = 1;
      ctx.strokeRect(16, 16, 260, 48);

      ctx.fillStyle = '#38bdf8';
      ctx.font = 'bold 11px sans-serif';
      ctx.fillText('PHOTOSYNTHESIS REACTION:', 26, 34);
      ctx.fillStyle = '#f8fafc';
      ctx.font = 'bold 12px monospace';
      ctx.fillText('6CO₂ + 6H₂O + Light ➔ C₆H₁₂O₆ + 6O₂', 26, 52);

      activeAnimationId = requestAnimationFrame(render);
    }

    render();
  }

  // --- LAB 2: Simple Machines Playground ---
  function initSimpleMachinesLab(canvas, controlsContainer, challengeContainer) {
    stopCurrentSimulation();
    const ctx = canvas.getContext('2d');

    const state = {
      machineType: 'lever', // 'lever' | 'ramp' | 'pulley'
      leverClass: 1,
      fulcrumPos: 0.5, // 0.1 to 0.9 (relative position along beam)
      loadWeight: 60,  // Newtons
      effortForce: 60, // Newtons
      rampAngle: 30,   // degrees
      rampLength: 4,   // meters
      pulleyStrings: 2,// 1 = fixed, 2 = movable, 4 = compound
      time: 0
    };
    currentLabState = state;

    function renderControls() {
      if (state.machineType === 'lever') {
        const loadArm = (state.fulcrumPos * 4).toFixed(1);
        const effortArm = ((1 - state.fulcrumPos) * 4).toFixed(1);
        const idealEffort = (state.loadWeight * (loadArm / effortArm)).toFixed(1);
        const ma = (effortArm / loadArm).toFixed(2);

        controlsContainer.innerHTML = `
          <div class="sim-tab-row">
            <button class="btn btn-sm ${state.machineType === 'lever' ? 'btn-primary' : 'btn-secondary'}" onclick="SciSimulations.setMachineType('lever')"><i class="fa-solid fa-bars"></i> Lever</button>
            <button class="btn btn-sm ${state.machineType === 'ramp' ? 'btn-primary' : 'btn-secondary'}" onclick="SciSimulations.setMachineType('ramp')"><i class="fa-solid fa-road"></i> Inclined Plane</button>
            <button class="btn btn-sm ${state.machineType === 'pulley' ? 'btn-primary' : 'btn-secondary'}" onclick="SciSimulations.setMachineType('pulley')"><i class="fa-solid fa-dharmachakra"></i> Pulley</button>
          </div>
          <div class="sim-control-group mt-14">
            <label>Fulcrum Position: <span class="sim-val">${Math.round(state.fulcrumPos * 100)}%</span></label>
            <input type="range" id="lever-fulcrum" min="15" max="85" value="${Math.round(state.fulcrumPos * 100)}" class="form-control sim-slider">
          </div>
          <div class="sim-control-group">
            <label>Load Weight: <span class="sim-val">${state.loadWeight} N</span></label>
            <input type="range" id="lever-load" min="10" max="150" value="${state.loadWeight}" class="form-control sim-slider">
          </div>
          <div class="sim-control-group">
            <label>Applied Effort: <span class="sim-val">${state.effortForce} N</span></label>
            <input type="range" id="lever-effort" min="5" max="150" value="${state.effortForce}" class="form-control sim-slider">
          </div>
          <div class="sim-stat-card">
            <div class="sim-metric"><span class="metric-label">Mechanical Advantage (MA)</span><span class="metric-val text-primary">${ma}x</span></div>
            <div class="sim-metric mt-10"><span class="metric-label">Effort Required to Lift</span><span class="metric-val text-warning">${idealEffort} N</span></div>
            <div class="sim-metric mt-10"><span class="metric-label">State</span><span class="metric-val ${state.effortForce >= idealEffort ? 'text-success' : 'text-danger'}">${state.effortForce >= idealEffort ? '✅ Load Lifted!' : '⚠️ Insufficient Effort'}</span></div>
          </div>
        `;

        document.getElementById('lever-fulcrum').oninput = (e) => {
          state.fulcrumPos = parseInt(e.target.value) / 100;
          renderControls();
        };
        document.getElementById('lever-load').oninput = (e) => {
          state.loadWeight = parseInt(e.target.value);
          renderControls();
        };
        document.getElementById('lever-effort').oninput = (e) => {
          state.effortForce = parseInt(e.target.value);
          renderControls();
        };
      } else if (state.machineType === 'ramp') {
        const height = (state.rampLength * Math.sin((state.rampAngle * Math.PI) / 180)).toFixed(1);
        const ma = (state.rampLength / height).toFixed(2);
        const requiredPull = (state.loadWeight * Math.sin((state.rampAngle * Math.PI) / 180)).toFixed(1);

        controlsContainer.innerHTML = `
          <div class="sim-tab-row">
            <button class="btn btn-sm ${state.machineType === 'lever' ? 'btn-primary' : 'btn-secondary'}" onclick="SciSimulations.setMachineType('lever')"><i class="fa-solid fa-bars"></i> Lever</button>
            <button class="btn btn-sm ${state.machineType === 'ramp' ? 'btn-primary' : 'btn-secondary'}" onclick="SciSimulations.setMachineType('ramp')"><i class="fa-solid fa-road"></i> Inclined Plane</button>
            <button class="btn btn-sm ${state.machineType === 'pulley' ? 'btn-primary' : 'btn-secondary'}" onclick="SciSimulations.setMachineType('pulley')"><i class="fa-solid fa-dharmachakra"></i> Pulley</button>
          </div>
          <div class="sim-control-group mt-14">
            <label>Ramp Slope Angle: <span class="sim-val">${state.rampAngle}°</span></label>
            <input type="range" id="ramp-angle" min="10" max="65" value="${state.rampAngle}" class="form-control sim-slider">
          </div>
          <div class="sim-control-group">
            <label>Load Weight: <span class="sim-val">${state.loadWeight} N</span></label>
            <input type="range" id="ramp-load" min="10" max="200" value="${state.loadWeight}" class="form-control sim-slider">
          </div>
          <div class="sim-stat-card">
            <div class="sim-metric"><span class="metric-label">Mechanical Advantage ($L / H$)</span><span class="metric-val text-primary">${ma}x</span></div>
            <div class="sim-metric mt-10"><span class="metric-label">Pulling Force Required</span><span class="metric-val text-success">${requiredPull} N</span></div>
            <div class="sim-metric mt-10"><span class="metric-label">Force Saved</span><span class="metric-val text-warning">${(state.loadWeight - requiredPull).toFixed(1)} N</span></div>
          </div>
        `;

        document.getElementById('ramp-angle').oninput = (e) => {
          state.rampAngle = parseInt(e.target.value);
          renderControls();
        };
        document.getElementById('ramp-load').oninput = (e) => {
          state.loadWeight = parseInt(e.target.value);
          renderControls();
        };
      } else { // Pulley
        const ma = state.pulleyStrings;
        const requiredEffort = (state.loadWeight / ma).toFixed(1);

        controlsContainer.innerHTML = `
          <div class="sim-tab-row">
            <button class="btn btn-sm ${state.machineType === 'lever' ? 'btn-primary' : 'btn-secondary'}" onclick="SciSimulations.setMachineType('lever')"><i class="fa-solid fa-bars"></i> Lever</button>
            <button class="btn btn-sm ${state.machineType === 'ramp' ? 'btn-primary' : 'btn-secondary'}" onclick="SciSimulations.setMachineType('ramp')"><i class="fa-solid fa-road"></i> Inclined Plane</button>
            <button class="btn btn-sm ${state.machineType === 'pulley' ? 'btn-primary' : 'btn-secondary'}" onclick="SciSimulations.setMachineType('pulley')"><i class="fa-solid fa-dharmachakra"></i> Pulley</button>
          </div>
          <div class="sim-control-group mt-14">
            <label>Pulley Configuration:</label>
            <div style="display:flex; gap:6px; margin-top:6px;">
              <button class="btn btn-sm ${state.pulleyStrings === 1 ? 'btn-primary' : 'btn-secondary'} flex-1" onclick="SciSimulations.setPulleyType(1)">Single Fixed (MA=1)</button>
              <button class="btn btn-sm ${state.pulleyStrings === 2 ? 'btn-primary' : 'btn-secondary'} flex-1" onclick="SciSimulations.setPulleyType(2)">Movable (MA=2)</button>
              <button class="btn btn-sm ${state.pulleyStrings === 4 ? 'btn-primary' : 'btn-secondary'} flex-1" onclick="SciSimulations.setPulleyType(4)">Block & Tackle (MA=4)</button>
            </div>
          </div>
          <div class="sim-control-group">
            <label>Load Weight: <span class="sim-val">${state.loadWeight} N</span></label>
            <input type="range" id="pulley-load" min="20" max="200" value="${state.loadWeight}" class="form-control sim-slider">
          </div>
          <div class="sim-stat-card">
            <div class="sim-metric"><span class="metric-label">Mechanical Advantage (MA)</span><span class="metric-val text-primary">${ma}x</span></div>
            <div class="sim-metric mt-10"><span class="metric-label">Lifting Effort Required</span><span class="metric-val text-success">${requiredEffort} N</span></div>
          </div>
        `;

        document.getElementById('pulley-load').oninput = (e) => {
          state.loadWeight = parseInt(e.target.value);
          renderControls();
        };
      }
    }

    challengeContainer.innerHTML = `
      <div class="sim-challenge-card">
        <h4><i class="fa-solid fa-clipboard-question text-primary"></i> Oral Recitation Challenge</h4>
        <p><strong>Question:</strong> How does moving the fulcrum closer to the heavy load make it easier for a person to lift it?</p>
        <div class="sim-answer-hint">
          <small>💡 <em>Hint: Moving the fulcrum closer to the load increases the effort arm length, multiplying the Mechanical Advantage ($MA = \text{Effort Arm} / \text{Load Arm}$).</em></small>
        </div>
      </div>
    `;

    renderControls();

    function render() {
      state.time += 0.02;
      const w = canvas.width;
      const h = canvas.height;

      ctx.fillStyle = '#0f172a';
      ctx.fillRect(0, 0, w, h);

      if (state.machineType === 'lever') {
        const beamY = h * 0.58;
        const beamStartX = w * 0.15;
        const beamEndX = w * 0.85;
        const beamLength = beamEndX - beamStartX;
        const fulcrumX = beamStartX + beamLength * state.fulcrumPos;

        const loadArm = state.fulcrumPos * 4;
        const effortArm = (1 - state.fulcrumPos) * 4;
        const idealEffort = state.loadWeight * (loadArm / effortArm);
        const isLifted = state.effortForce >= idealEffort;
        const tiltAngle = isLifted ? 0.15 : -0.15;

        // Fulcrum Triangle
        ctx.fillStyle = '#f59e0b';
        ctx.beginPath();
        ctx.moveTo(fulcrumX, beamY);
        ctx.lineTo(fulcrumX - 25, beamY + 45);
        ctx.lineTo(fulcrumX + 25, beamY + 45);
        ctx.closePath();
        ctx.fill();

        // Floor
        ctx.fillStyle = '#334155';
        ctx.fillRect(w * 0.05, beamY + 45, w * 0.9, 10);

        // Beam
        ctx.save();
        ctx.translate(fulcrumX, beamY);
        ctx.rotate(tiltAngle);

        ctx.fillStyle = '#38bdf8';
        ctx.fillRect(-state.fulcrumPos * beamLength, -8, beamLength, 16);

        // Load Box (Left side)
        const boxSize = 28 + (state.loadWeight / 150) * 20;
        ctx.fillStyle = '#ef4444';
        ctx.fillRect(-state.fulcrumPos * beamLength + 8, -8 - boxSize, boxSize, boxSize);
        ctx.fillStyle = '#fff';
        ctx.font = 'bold 11px sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText(`Load: ${state.loadWeight}N`, -state.fulcrumPos * beamLength + 8 + boxSize / 2, -14 - boxSize / 2);

        // Effort Arrow (Right side)
        const effortX = (1 - state.fulcrumPos) * beamLength - 16;
        ctx.fillStyle = '#10b981';
        ctx.fillRect(effortX - 8, -8 - 40, 16, 40);
        // Arrow head pointing down
        ctx.beginPath();
        ctx.moveTo(effortX - 16, -8);
        ctx.lineTo(effortX + 16, -8);
        ctx.lineTo(effortX, 4);
        ctx.closePath();
        ctx.fill();

        ctx.fillStyle = '#fff';
        ctx.fillText(`Effort: ${state.effortForce}N`, effortX, -55);

        ctx.restore();
      } else if (state.machineType === 'ramp') {
        const rampBaseX = w * 0.15;
        const rampGroundY = h * 0.72;
        const rampWidth = w * 0.65;
        const angleRad = (state.rampAngle * Math.PI) / 180;
        const rampHeight = Math.tan(angleRad) * rampWidth;

        // Ramp triangle
        ctx.fillStyle = '#1e293b';
        ctx.strokeStyle = '#38bdf8';
        ctx.lineWidth = 3;
        ctx.beginPath();
        ctx.moveTo(rampBaseX, rampGroundY);
        ctx.lineTo(rampBaseX + rampWidth, rampGroundY);
        ctx.lineTo(rampBaseX + rampWidth, rampGroundY - rampHeight);
        ctx.closePath();
        ctx.fill();
        ctx.stroke();

        // Floor
        ctx.fillStyle = '#334155';
        ctx.fillRect(w * 0.05, rampGroundY, w * 0.9, 10);

        // Box sliding up ramp
        const animProgress = (Math.sin(state.time * 1.5) + 1) / 2;
        const boxHypot = animProgress * (rampWidth / Math.cos(angleRad));
        const boxX = rampBaseX + boxHypot * Math.cos(angleRad);
        const boxY = rampGroundY - boxHypot * Math.sin(angleRad);

        ctx.save();
        ctx.translate(boxX, boxY);
        ctx.rotate(-angleRad);
        ctx.fillStyle = '#ef4444';
        ctx.fillRect(0, -32, 40, 32);
        ctx.fillStyle = '#fff';
        ctx.font = 'bold 10px sans-serif';
        ctx.fillText(`${state.loadWeight}N`, 20, -14);
        ctx.restore();
      } else { // Pulley
        const ceilingY = h * 0.2;
        ctx.fillStyle = '#334155';
        ctx.fillRect(w * 0.2, ceilingY - 10, w * 0.6, 10);

        // Pulley Wheel
        const wheelX = w * 0.5;
        const wheelY = ceilingY + 30;
        ctx.beginPath();
        ctx.arc(wheelX, wheelY, 24, 0, Math.PI * 2);
        ctx.fillStyle = '#f59e0b';
        ctx.fill();
        ctx.strokeStyle = '#fff';
        ctx.lineWidth = 3;
        ctx.stroke();

        // Load
        const liftY = h * 0.65 - Math.sin(state.time * 2) * 30;
        ctx.fillStyle = '#ef4444';
        ctx.fillRect(wheelX - 35, liftY, 40, 35);
        ctx.fillStyle = '#fff';
        ctx.font = 'bold 11px sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText(`${state.loadWeight}N`, wheelX - 15, liftY + 22);

        // Ropes
        ctx.strokeStyle = '#f8fafc';
        ctx.lineWidth = 3;
        ctx.beginPath();
        ctx.moveTo(wheelX - 24, liftY);
        ctx.lineTo(wheelX - 24, wheelY);
        ctx.arc(wheelX, wheelY, 24, Math.PI, 0, false);
        ctx.lineTo(wheelX + 24, h * 0.7);
        ctx.stroke();
      }

      activeAnimationId = requestAnimationFrame(render);
    }

    render();
  }

  // --- LAB 3: States of Matter & Particle Simulator ---
  function initStatesOfMatterLab(canvas, controlsContainer, challengeContainer) {
    stopCurrentSimulation();
    const ctx = canvas.getContext('2d');

    const state = {
      stateType: 'solid', // 'solid' | 'liquid' | 'gas'
      temperature: 20,    // °C (-20 to 150)
      particles: [],
      numParticles: 64,
      time: 0
    };
    currentLabState = state;

    function initParticles() {
      state.particles = [];
      const cols = 8;
      const rows = 8;
      for (let r = 0; r < rows; r++) {
        for (let c = 0; c < cols; c++) {
          state.particles.push({
            originX: 160 + c * 24,
            originY: 180 + r * 24,
            x: 160 + c * 24,
            y: 180 + r * 24,
            vx: (Math.random() - 0.5) * 2,
            vy: (Math.random() - 0.5) * 2,
            radius: 7
          });
        }
      }
    }
    initParticles();

    function renderControls() {
      const kelvin = state.temperature + 273;
      controlsContainer.innerHTML = `
        <div class="sim-tab-row">
          <button class="btn btn-sm ${state.stateType === 'solid' ? 'btn-primary' : 'btn-secondary'} flex-1" onclick="SciSimulations.setStateOfMatter('solid')"><i class="fa-solid fa-cube"></i> Solid (Ice)</button>
          <button class="btn btn-sm ${state.stateType === 'liquid' ? 'btn-primary' : 'btn-secondary'} flex-1" onclick="SciSimulations.setStateOfMatter('liquid')"><i class="fa-solid fa-droplet"></i> Liquid (Water)</button>
          <button class="btn btn-sm ${state.stateType === 'gas' ? 'btn-primary' : 'btn-secondary'} flex-1" onclick="SciSimulations.setStateOfMatter('gas')"><i class="fa-solid fa-cloud"></i> Gas (Steam)</button>
        </div>
        <div class="sim-control-group mt-14">
          <label><i class="fa-solid fa-temperature-three-quarters text-danger"></i> Temperature: <span class="sim-val">${state.temperature}°C (${kelvin} K)</span></label>
          <input type="range" id="matter-temp" min="-30" max="150" value="${state.temperature}" class="form-control sim-slider">
        </div>
        <div class="sim-stat-card">
          <div class="sim-metric"><span class="metric-label">Molecular Kinetic Energy</span><span class="metric-val text-primary">${state.stateType === 'solid' ? 'Low (Vibrational only)' : state.stateType === 'liquid' ? 'Moderate (Fluid sliding)' : 'High (Free rapid bouncing)'}</span></div>
          <div class="sim-metric mt-10"><span class="metric-label">Intermolecular Force</span><span class="metric-val text-success">${state.stateType === 'solid' ? 'Strong Attraction' : state.stateType === 'liquid' ? 'Moderate Attraction' : 'Negligible Attraction'}</span></div>
          <div class="sim-metric mt-10"><span class="metric-label">Shape & Volume</span><span class="metric-val text-warning">${state.stateType === 'solid' ? 'Definite Shape & Volume' : state.stateType === 'liquid' ? 'Indefinite Shape, Definite Volume' : 'Indefinite Shape & Volume'}</span></div>
        </div>
      `;

      document.getElementById('matter-temp').oninput = (e) => {
        state.temperature = parseInt(e.target.value);
        if (state.temperature <= 0) state.stateType = 'solid';
        else if (state.temperature < 100) state.stateType = 'liquid';
        else state.stateType = 'gas';
        renderControls();
      };
    }

    challengeContainer.innerHTML = `
      <div class="sim-challenge-card">
        <h4><i class="fa-solid fa-clipboard-question text-primary"></i> Oral Recitation Challenge</h4>
        <p><strong>Question:</strong> What happens to the speed and spacing of molecules when an ice cube melts into water and then boils into gas?</p>
        <div class="sim-answer-hint">
          <small>💡 <em>Hint: Adding thermal energy increases kinetic speed, overcoming intermolecular attraction forces and increasing particle spacing.</em></small>
        </div>
      </div>
    `;

    renderControls();

    function render() {
      state.time += 0.03;
      const w = canvas.width;
      const h = canvas.height;

      ctx.fillStyle = '#0f172a';
      ctx.fillRect(0, 0, w, h);

      // Beaker / Container
      const boxX = w * 0.22;
      const boxY = h * 0.18;
      const boxW = w * 0.56;
      const boxH = h * 0.64;

      ctx.strokeStyle = '#94a3b8';
      ctx.lineWidth = 4;
      ctx.strokeRect(boxX, boxY, boxW, boxH);

      // Flame underneath if hot
      if (state.temperature > 40) {
        const flameY = boxY + boxH + 8;
        const flameHeight = Math.min(40, (state.temperature / 150) * 40);
        ctx.fillStyle = '#f59e0b';
        ctx.beginPath();
        ctx.moveTo(w * 0.4, flameY);
        ctx.quadraticCurveTo(w * 0.5 + Math.sin(state.time * 6) * 10, flameY + flameHeight, w * 0.6, flameY);
        ctx.fill();
      }

      // Render Particles
      state.particles.forEach((p, idx) => {
        let px = p.x;
        let py = p.y;

        if (state.stateType === 'solid') {
          // Vibrating in fixed grid
          const vibSpeed = Math.max(0.5, (state.temperature + 40) * 0.04);
          px = p.originX + Math.sin(state.time * 8 + idx) * vibSpeed;
          py = p.originY + Math.cos(state.time * 8 + idx) * vibSpeed;
          ctx.fillStyle = '#38bdf8';
        } else if (state.stateType === 'liquid') {
          // Rolling at the bottom of container
          p.x += p.vx * 1.5;
          p.y += p.vy * 1.5;
          p.vy += 0.1; // gravity

          if (p.x < boxX + 12) { p.x = boxX + 12; p.vx *= -1; }
          if (p.x > boxX + boxW - 12) { p.x = boxX + boxW - 12; p.vx *= -1; }
          if (p.y < boxY + boxH * 0.4) { p.y = boxY + boxH * 0.4; p.vy *= -1; }
          if (p.y > boxY + boxH - 12) { p.y = boxY + boxH - 12; p.vy *= -0.8; }
          px = p.x; py = p.y;
          ctx.fillStyle = '#06d6a0';
        } else { // gas
          p.x += p.vx * 4;
          p.y += p.vy * 4;

          if (p.x < boxX + 10 || p.x > boxX + boxW - 10) p.vx *= -1;
          if (p.y < boxY + 10 || p.y > boxY + boxH - 10) p.vy *= -1;
          px = p.x; py = p.y;
          ctx.fillStyle = '#f43f5e';
        }

        ctx.beginPath();
        ctx.arc(px, py, p.radius, 0, Math.PI * 2);
        ctx.fill();
      });

      activeAnimationId = requestAnimationFrame(render);
    }

    render();
  }

  // --- LAB 4: The Water Cycle & Weather Laboratory ---
  function initWaterCycleLab(canvas, controlsContainer, challengeContainer) {
    stopCurrentSimulation();
    const ctx = canvas.getContext('2d');

    const state = {
      solarHeat: 75,
      windSpeed: 40,
      activePhase: 'all',
      time: 0,
      rainDrops: [],
      vaporParticles: []
    };
    currentLabState = state;

    controlsContainer.innerHTML = `
      <div class="sim-control-group">
        <label><i class="fa-solid fa-sun text-warning"></i> Solar Radiation: <span id="cycle-sun-val" class="sim-val">${state.solarHeat}%</span></label>
        <input type="range" id="cycle-sun" min="10" max="100" value="${state.solarHeat}" class="form-control sim-slider">
      </div>
      <div class="sim-control-group">
        <label><i class="fa-solid fa-wind" style="color:#38bdf8;"></i> Wind Speed: <span id="cycle-wind-val" class="sim-val">${state.windSpeed} km/h</span></label>
        <input type="range" id="cycle-wind" min="0" max="80" value="${state.windSpeed}" class="form-control sim-slider">
      </div>
      <div class="sim-stat-card">
        <div class="sim-metric"><span class="metric-label">Active Phase Highlights</span></div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:6px; margin-top:8px;">
          <button class="btn btn-sm btn-secondary" onclick="SciSimulations.setCyclePhase('evaporation')">1. Evaporation</button>
          <button class="btn btn-sm btn-secondary" onclick="SciSimulations.setCyclePhase('condensation')">2. Condensation</button>
          <button class="btn btn-sm btn-secondary" onclick="SciSimulations.setCyclePhase('precipitation')">3. Precipitation</button>
          <button class="btn btn-sm btn-secondary" onclick="SciSimulations.setCyclePhase('collection')">4. Collection</button>
        </div>
      </div>
    `;

    challengeContainer.innerHTML = `
      <div class="sim-challenge-card">
        <h4><i class="fa-solid fa-clipboard-question text-primary"></i> Oral Recitation Challenge</h4>
        <p><strong>Question:</strong> Name the 4 main stages of the water cycle and explain what drives evaporation.</p>
        <div class="sim-answer-hint">
          <small>💡 <em>Hint: Evaporation, Condensation, Precipitation, and Collection/Runoff. Solar thermal energy from the Sun powers evaporation.</em></small>
        </div>
      </div>
    `;

    document.getElementById('cycle-sun').oninput = (e) => {
      state.solarHeat = parseInt(e.target.value);
      document.getElementById('cycle-sun-val').textContent = `${state.solarHeat}%`;
    };
    document.getElementById('cycle-wind').oninput = (e) => {
      state.windSpeed = parseInt(e.target.value);
      document.getElementById('cycle-wind-val').textContent = `${state.windSpeed} km/h`;
    };

    function render() {
      state.time += 0.02;
      const w = canvas.width;
      const h = canvas.height;

      // Sky
      const skyGrad = ctx.createLinearGradient(0, 0, 0, h * 0.7);
      skyGrad.addColorStop(0, '#0284c7');
      skyGrad.addColorStop(1, '#bae6fd');
      ctx.fillStyle = skyGrad;
      ctx.fillRect(0, 0, w, h * 0.7);

      // Sun
      ctx.beginPath();
      ctx.arc(w * 0.82, h * 0.16, 32, 0, Math.PI * 2);
      ctx.fillStyle = '#f59e0b';
      ctx.shadowColor = '#fbbf24';
      ctx.shadowBlur = 25;
      ctx.fill();
      ctx.shadowBlur = 0;

      // Ocean on right
      const oceanY = h * 0.65;
      ctx.fillStyle = '#0284c7';
      ctx.fillRect(w * 0.45, oceanY, w * 0.55, h - oceanY);

      // Mountains on left
      ctx.fillStyle = '#475569';
      ctx.beginPath();
      ctx.moveTo(0, oceanY + 20);
      ctx.lineTo(w * 0.25, h * 0.28);
      ctx.lineTo(w * 0.5, oceanY + 20);
      ctx.closePath();
      ctx.fill();

      // Cloud
      const cloudX = w * 0.35 + Math.sin(state.time * 0.8) * 15;
      const cloudY = h * 0.22;
      ctx.fillStyle = 'rgba(255, 255, 255, 0.85)';
      ctx.beginPath();
      ctx.arc(cloudX, cloudY, 28, 0, Math.PI * 2);
      ctx.arc(cloudX + 30, cloudY - 10, 34, 0, Math.PI * 2);
      ctx.arc(cloudX + 60, cloudY, 26, 0, Math.PI * 2);
      ctx.fill();

      // Evaporation particles rising from ocean
      if (Math.random() < (state.solarHeat / 100) * 0.4) {
        state.vaporParticles.push({
          x: w * 0.5 + Math.random() * (w * 0.45),
          y: oceanY,
          speed: 1 + Math.random() * 1.5,
          opacity: 0.8
        });
      }

      for (let v = state.vaporParticles.length - 1; v >= 0; v--) {
        const vp = state.vaporParticles[v];
        vp.y -= vp.speed;
        vp.x -= (state.windSpeed / 50) * 0.8;
        vp.opacity -= 0.007;

        ctx.fillStyle = `rgba(255, 255, 255, ${vp.opacity})`;
        ctx.beginPath();
        ctx.arc(vp.x, vp.y, 3, 0, Math.PI * 2);
        ctx.fill();

        if (vp.opacity <= 0 || vp.y < cloudY) state.vaporParticles.splice(v, 1);
      }

      // Rain falling from cloud
      if (Math.random() < 0.6) {
        state.rainDrops.push({
          x: cloudX - 15 + Math.random() * 85,
          y: cloudY + 20,
          speed: 4 + Math.random() * 3
        });
      }

      ctx.strokeStyle = '#38bdf8';
      ctx.lineWidth = 2;
      for (let r = state.rainDrops.length - 1; r >= 0; r--) {
        const drop = state.rainDrops[r];
        drop.y += drop.speed;
        drop.x += (state.windSpeed / 50) * 0.5;

        ctx.beginPath();
        ctx.moveTo(drop.x, drop.y);
        ctx.lineTo(drop.x + 1, drop.y + 6);
        ctx.stroke();

        if (drop.y > oceanY) state.rainDrops.splice(r, 1);
      }

      activeAnimationId = requestAnimationFrame(render);
    }

    render();
  }

  // --- LAB 5: Electric Circuit Explorer ---
  function initCircuitsLab(canvas, controlsContainer, challengeContainer) {
    stopCurrentSimulation();
    const ctx = canvas.getContext('2d');

    const state = {
      batteryVoltage: 9, // Volts
      switchClosed: true,
      resistance: 15,   // Ohms
      circuitType: 'series',
      electrons: [],
      time: 0
    };
    currentLabState = state;

    // Generate electrons around square wire loop
    for (let i = 0; i < 28; i++) {
      state.electrons.push((i / 28));
    }

    function renderControls() {
      const current = state.switchClosed ? (state.batteryVoltage / state.resistance).toFixed(2) : '0.00';
      const power = state.switchClosed ? (state.batteryVoltage * current).toFixed(1) : '0.0';

      controlsContainer.innerHTML = `
        <div class="sim-control-group">
          <label><i class="fa-solid fa-car-battery text-warning"></i> Battery Voltage: <span class="sim-val">${state.batteryVoltage}V</span></label>
          <input type="range" id="circuit-volt" min="1.5" max="24" step="1.5" value="${state.batteryVoltage}" class="form-control sim-slider">
        </div>
        <div class="sim-control-group">
          <label><i class="fa-solid fa-bolt" style="color:#06d6a0;"></i> Switch Position:</label>
          <button class="btn btn-sm ${state.switchClosed ? 'btn-success' : 'btn-danger'} w-100 mt-6" id="circuit-switch">
            ${state.switchClosed ? '🟢 Switch CLOSED (Conducting)' : '🔴 Switch OPEN (Broken Circuit)'}
          </button>
        </div>
        <div class="sim-control-group">
          <label>Resistor Resistance: <span class="sim-val">${state.resistance} Ω</span></label>
          <input type="range" id="circuit-res" min="5" max="50" value="${state.resistance}" class="form-control sim-slider">
        </div>
        <div class="sim-stat-card">
          <div class="sim-metric"><span class="metric-label">Current ($I = V / R$)</span><span class="metric-val text-primary">${current} Amperes</span></div>
          <div class="sim-metric mt-10"><span class="metric-label">Bulb Power ($P = V \times I$)</span><span class="metric-val text-warning">${power} Watts</span></div>
          <div class="sim-metric mt-10"><span class="metric-label">Bulb Brightness</span><span class="metric-val text-success">${state.switchClosed ? (power > 15 ? '🌟 Very Bright' : '💡 Glowing') : '⚫ Off'}</span></div>
        </div>
      `;

      document.getElementById('circuit-volt').oninput = (e) => {
        state.batteryVoltage = parseFloat(e.target.value);
        renderControls();
      };
      document.getElementById('circuit-switch').onclick = () => {
        state.switchClosed = !state.switchClosed;
        playSound(state.switchClosed ? 'click' : 'tick');
        renderControls();
      };
      document.getElementById('circuit-res').oninput = (e) => {
        state.resistance = parseInt(e.target.value);
        renderControls();
      };
    }

    challengeContainer.innerHTML = `
      <div class="sim-challenge-card">
        <h4><i class="fa-solid fa-clipboard-question text-primary"></i> Oral Recitation Challenge</h4>
        <p><strong>Question:</strong> What happens to the electric current and bulb brightness when you increase the resistance of the circuit?</p>
        <div class="sim-answer-hint">
          <small>💡 <em>Hint: Ohm's Law ($I = V/R$) states that current is inversely proportional to resistance. Higher resistance decreases current flow and dims the bulb.</em></small>
        </div>
      </div>
    `;

    renderControls();

    function render() {
      state.time += 0.02;
      const w = canvas.width;
      const h = canvas.height;

      ctx.fillStyle = '#0f172a';
      ctx.fillRect(0, 0, w, h);

      const wireX = w * 0.18;
      const wireY = h * 0.22;
      const wireW = w * 0.64;
      const wireH = h * 0.56;

      // Wire Loop
      ctx.strokeStyle = '#64748b';
      ctx.lineWidth = 6;
      ctx.strokeRect(wireX, wireY, wireW, wireH);

      // Battery on Left Wire
      const batY = wireY + wireH * 0.5;
      ctx.fillStyle = '#f59e0b';
      ctx.fillRect(wireX - 16, batY - 30, 32, 60);
      ctx.fillStyle = '#0f172a';
      ctx.font = 'bold 12px sans-serif';
      ctx.textAlign = 'center';
      ctx.fillText(`${state.batteryVoltage}V`, wireX, batY + 4);

      // Switch on Top Wire
      const swX = wireX + wireW * 0.5;
      ctx.fillStyle = '#0f172a';
      ctx.fillRect(swX - 25, wireY - 12, 50, 24);
      ctx.strokeStyle = '#ef4444';
      ctx.lineWidth = 4;
      ctx.beginPath();
      ctx.moveTo(swX - 20, wireY);
      if (state.switchClosed) {
        ctx.lineTo(swX + 20, wireY);
      } else {
        ctx.lineTo(swX + 15, wireY - 22);
      }
      ctx.stroke();

      // Lightbulb on Right Wire
      const bulbY = wireY + wireH * 0.5;
      const current = state.switchClosed ? state.batteryVoltage / state.resistance : 0;
      const glow = Math.min(40, current * 12);

      ctx.beginPath();
      ctx.arc(wireX + wireW, bulbY, 20, 0, Math.PI * 2);
      ctx.fillStyle = state.switchClosed ? `rgba(251, 191, 36, ${Math.min(1, 0.3 + current * 0.4)})` : '#334155';
      if (state.switchClosed && current > 0) {
        ctx.shadowColor = '#fbbf24';
        ctx.shadowBlur = glow;
      }
      ctx.fill();
      ctx.shadowBlur = 0;
      ctx.strokeStyle = '#f8fafc';
      ctx.lineWidth = 2;
      ctx.stroke();

      // Electron particle dots along wire
      if (state.switchClosed && current > 0) {
        const speed = (current / 2) * 0.003;
        ctx.fillStyle = '#38bdf8';
        const perimeter = (wireW + wireH) * 2;

        state.electrons.forEach((prog, idx) => {
          state.electrons[idx] = (prog + speed) % 1.0;
          const pDist = state.electrons[idx] * perimeter;

          let ex = wireX;
          let ey = wireY;

          if (pDist < wireW) { // top wire
            ex = wireX + pDist;
            ey = wireY;
          } else if (pDist < wireW + wireH) { // right wire
            ex = wireX + wireW;
            ey = wireY + (pDist - wireW);
          } else if (pDist < wireW * 2 + wireH) { // bottom wire
            ex = wireX + wireW - (pDist - (wireW + wireH));
            ey = wireY + wireH;
          } else { // left wire
            ex = wireX;
            ey = wireY + wireH - (pDist - (wireW * 2 + wireH));
          }

          ctx.beginPath();
          ctx.arc(ex, ey, 4, 0, Math.PI * 2);
          ctx.fill();
        });
      }

      activeAnimationId = requestAnimationFrame(render);
    }

    render();
  }

  // --- Public Launcher API ---
  function openLab(labId) {
    activeLabId = labId;
    const modal = document.getElementById('native-lab-modal');
    const canvas = document.getElementById('native-lab-canvas');
    const controls = document.getElementById('native-lab-controls');
    const challenge = document.getElementById('native-lab-challenge');
    const titleEl = document.getElementById('native-lab-title');

    if (!modal || !canvas || !controls || !challenge) return;

    // Resize canvas to high resolution
    canvas.width = 680;
    canvas.height = 460;

    modal.classList.remove('hidden');

    if (labId === 'photosynthesis') {
      titleEl.textContent = '🌱 Photosynthesis & Plant Growth Laboratory';
      initPhotosynthesisLab(canvas, controls, challenge);
    } else if (labId === 'machines') {
      titleEl.textContent = '⚙️ Simple Machines Interactive Playground';
      initSimpleMachinesLab(canvas, controls, challenge);
    } else if (labId === 'matter') {
      titleEl.textContent = '🔬 States of Matter & Molecular Simulator';
      initStatesOfMatterLab(canvas, controls, challenge);
    } else if (labId === 'water') {
      titleEl.textContent = '💧 The Water Cycle & Weather Laboratory';
      initWaterCycleLab(canvas, controls, challenge);
    } else if (labId === 'circuits') {
      titleEl.textContent = '⚡ Electric Circuit Explorer';
      initCircuitsLab(canvas, controls, challenge);
    }
  }

  function closeLab() {
    stopCurrentSimulation();
    const modal = document.getElementById('native-lab-modal');
    if (modal) modal.classList.add('hidden');
  }

  return {
    openLab,
    closeLab,
    setMachineType: function (type) {
      if (currentLabState) {
        currentLabState.machineType = type;
        const canvas = document.getElementById('native-lab-canvas');
        const controls = document.getElementById('native-lab-controls');
        const challenge = document.getElementById('native-lab-challenge');
        initSimpleMachinesLab(canvas, controls, challenge);
      }
    },
    setPulleyType: function (strings) {
      if (currentLabState) {
        currentLabState.pulleyStrings = strings;
        const canvas = document.getElementById('native-lab-canvas');
        const controls = document.getElementById('native-lab-controls');
        const challenge = document.getElementById('native-lab-challenge');
        initSimpleMachinesLab(canvas, controls, challenge);
      }
    },
    setStateOfMatter: function (type) {
      if (currentLabState) {
        currentLabState.stateType = type;
        currentLabState.temperature = type === 'solid' ? -10 : type === 'liquid' ? 25 : 110;
        const canvas = document.getElementById('native-lab-canvas');
        const controls = document.getElementById('native-lab-controls');
        const challenge = document.getElementById('native-lab-challenge');
        initStatesOfMatterLab(canvas, controls, challenge);
      }
    },
    setCyclePhase: function (phase) {
      if (currentLabState) {
        currentLabState.activePhase = phase;
      }
    }
  };
})();

window.SciSimulations = SciSimulations;
