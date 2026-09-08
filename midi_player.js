/**
 * ILikeSci — Offline Web Audio MIDI Synthesizer & Player Engine (`midi_player.js`)
 * 
 * 100% Offline, Zero External SoundFonts/CDNs, Ultra-Lightweight (<15KB)
 * Specifically optimized for low-end hardware and DepEd science classrooms.
 * 
 * Capabilities:
 * - Parses SMF Type 0 and Type 1 Standard MIDI binary files.
 * - Multi-track chronological event scheduler with dynamic tempo mapping.
 * - Procedural polyphonic Web Audio synthesis with ADSR envelopes and filter shaping.
 * - Full transport controls: Play, Pause, Resume, Stop, Scrub/Seek, Speed/Tempo control.
 * - Low-CPU real-time HTML5 Canvas visualizer and active notes callback.
 */

(function(global) {
  'use strict';

  // MIDI note to frequency mapping table (A4 = 440Hz, Note 69)
  function midiToFreq(note) {
    return 440 * Math.pow(2, (note - 69) / 12);
  }

  // Variable-Length Quantity (VLQ) reader
  function readVLQ(view, offset) {
    let value = 0;
    let bytesRead = 0;
    while (offset + bytesRead < view.byteLength) {
      const byte = view.getUint8(offset + bytesRead);
      bytesRead++;
      value = (value << 7) | (byte & 0x7F);
      if (!(byte & 0x80)) break;
    }
    return { value, bytesRead };
  }

  // Standard MIDI File (SMF) Parser
  class MidiParser {
    static parse(arrayBuffer) {
      const view = new DataView(arrayBuffer);
      let offset = 0;

      // 1. Check MThd header
      const headerTag = String.fromCharCode(
        view.getUint8(offset), view.getUint8(offset + 1),
        view.getUint8(offset + 2), view.getUint8(offset + 3)
      );
      if (headerTag !== 'MThd') {
        throw new Error('Invalid MIDI file: MThd header missing.');
      }
      offset += 4;

      const headerLength = view.getUint32(offset);
      offset += 4;

      const format = view.getUint16(offset);
      const numTracks = view.getUint16(offset + 2);
      const division = view.getUint16(offset + 4);
      offset += headerLength;

      const tracks = [];
      const tempoEvents = []; // [{ tick, usPerQuarter }]

      // 2. Parse tracks
      for (let t = 0; t < numTracks && offset < view.byteLength; t++) {
        const trackTag = String.fromCharCode(
          view.getUint8(offset), view.getUint8(offset + 1),
          view.getUint8(offset + 2), view.getUint8(offset + 3)
        );
        offset += 4;

        const trackLength = view.getUint32(offset);
        offset += 4;
        const trackEnd = offset + trackLength;

        const trackEvents = [];
        let currentTick = 0;
        let lastRunningStatus = 0;
        let trackName = `Track ${t + 1}`;

        while (offset < trackEnd && offset < view.byteLength) {
          const vlq = readVLQ(view, offset);
          offset += vlq.bytesRead;
          currentTick += vlq.value;

          let statusByte = view.getUint8(offset);
          if (statusByte < 0x80) {
            // Running status
            statusByte = lastRunningStatus;
          } else {
            offset++;
            lastRunningStatus = statusByte;
          }

          if (statusByte === 0xFF) {
            // Meta Event
            const metaType = view.getUint8(offset++);
            const lenVlq = readVLQ(view, offset);
            offset += lenVlq.bytesRead;
            const metaLength = lenVlq.value;

            if (metaType === 0x51 && metaLength === 3) {
              // Set Tempo (microseconds per quarter note)
              const usPerQuarter = (view.getUint8(offset) << 16) | (view.getUint8(offset + 1) << 8) | view.getUint8(offset + 2);
              tempoEvents.push({ tick: currentTick, usPerQuarter });
            } else if (metaType === 0x03) {
              // Track Name
              let name = '';
              for (let i = 0; i < metaLength; i++) {
                name += String.fromCharCode(view.getUint8(offset + i));
              }
              trackName = name;
            } else if (metaType === 0x2F) {
              // End of track
              offset += metaLength;
              break;
            }
            offset += metaLength;
          } else if (statusByte === 0xF0 || statusByte === 0xF7) {
            // SysEx Event
            const lenVlq = readVLQ(view, offset);
            offset += lenVlq.bytesRead + lenVlq.value;
          } else {
            // Channel Voice Message
            const command = statusByte & 0xF0;
            const channel = statusByte & 0x0F;

            if (command === 0x90) {
              // Note On
              const note = view.getUint8(offset++);
              const velocity = view.getUint8(offset++);
              if (velocity > 0) {
                trackEvents.push({ tick: currentTick, type: 'noteOn', channel, note, velocity });
              } else {
                trackEvents.push({ tick: currentTick, type: 'noteOff', channel, note, velocity: 0 });
              }
            } else if (command === 0x80) {
              // Note Off
              const note = view.getUint8(offset++);
              const velocity = view.getUint8(offset++);
              trackEvents.push({ tick: currentTick, type: 'noteOff', channel, note, velocity });
            } else if (command === 0xC0) {
              // Program Change
              const program = view.getUint8(offset++);
              trackEvents.push({ tick: currentTick, type: 'programChange', channel, program });
            } else if (command === 0xB0 || command === 0xE0) {
              // Control change or pitch bend (2 data bytes)
              offset += 2;
            } else if (command === 0xD0 || command === 0xA0) {
              // Channel pressure or key aftertouch
              offset += 1;
            }
          }
        }
        tracks.push({ name: trackName, events: trackEvents });
        offset = trackEnd;
      }

      // 3. Build unified timeline with real-world seconds
      tempoEvents.sort((a, b) => a.tick - b.tick);
      if (tempoEvents.length === 0 || tempoEvents[0].tick !== 0) {
        tempoEvents.unshift({ tick: 0, usPerQuarter: 500000 }); // Default 120 BPM
      }

      function tickToSeconds(targetTick) {
        let sec = 0;
        let lastTick = 0;
        let currentUsPerQuarter = 500000;

        for (let i = 0; i < tempoEvents.length; i++) {
          const te = tempoEvents[i];
          if (targetTick <= te.tick) break;
          const ticksElapsed = te.tick - lastTick;
          sec += (ticksElapsed / division) * (currentUsPerQuarter / 1000000);
          lastTick = te.tick;
          currentUsPerQuarter = te.usPerQuarter;
        }

        const remainingTicks = targetTick - lastTick;
        sec += (remainingTicks / division) * (currentUsPerQuarter / 1000000);
        return sec;
      }

      // Collect all note events
      const allEvents = [];
      tracks.forEach((track, trackIdx) => {
        track.events.forEach(evt => {
          allEvents.push({
            ...evt,
            trackIdx,
            time: tickToSeconds(evt.tick)
          });
        });
      });

      allEvents.sort((a, b) => a.time - b.time || (a.type === 'noteOff' ? -1 : 1));

      // Calculate total song duration
      let maxTime = 0;
      allEvents.forEach(e => { if (e.time > maxTime) maxTime = e.time; });

      return {
        format,
        division,
        numTracks,
        duration: maxTime + 0.5,
        tracks,
        events: allEvents
      };
    }
  }

  // Offline Web Audio Synthesizer Voice Generator
  class MidiSynthesizer {
    constructor(audioCtx) {
      this.ctx = audioCtx;
      this.masterGain = this.ctx.createGain();
      this.masterGain.gain.value = 0.8;

      // Master low-pass filter to give a pleasing, warm acoustic sound
      this.filter = this.ctx.createBiquadFilter();
      this.filter.type = 'lowpass';
      this.filter.frequency.value = 4500;
      this.filter.Q.value = 1.0;

      // Master compressor to prevent clipping on loud chords
      this.compressor = this.ctx.createDynamicsCompressor();
      this.compressor.threshold.setValueAtTime(-18, this.ctx.currentTime);
      this.compressor.knee.setValueAtTime(30, this.ctx.currentTime);
      this.compressor.ratio.setValueAtTime(6, this.ctx.currentTime);
      this.compressor.attack.setValueAtTime(0.003, this.ctx.currentTime);
      this.compressor.release.setValueAtTime(0.2, this.ctx.currentTime);

      this.masterGain.connect(this.filter);
      this.filter.connect(this.compressor);
      this.compressor.connect(this.ctx.destination);

      // Active oscillator voices map: key `${channel}_${note}`
      this.activeVoices = new Map();
      this.maxVoices = 20; // Voice capping for low-end hardware performance
    }

    setVolume(val) {
      if (this.masterGain) {
        this.masterGain.gain.setValueAtTime(Math.max(0, Math.min(1, val)), this.ctx.currentTime);
      }
    }

    noteOn(channel, note, velocity = 80, program = 0) {
      const now = this.ctx.currentTime;
      const key = `${channel}_${note}`;

      // If voice already active, stop it cleanly
      if (this.activeVoices.has(key)) {
        this.noteOff(channel, note);
      }

      // Voice limit check: discard oldest voice if at capacity
      if (this.activeVoices.size >= this.maxVoices) {
        const oldestKey = this.activeVoices.keys().next().value;
        const [oldChan, oldNote] = oldestKey.split('_').map(Number);
        this.noteOff(oldChan, oldNote);
      }

      const freq = midiToFreq(note);
      const gainNode = this.ctx.createGain();
      const osc = this.ctx.createOscillator();
      const velFactor = (velocity / 127);

      // Select waveform & envelope based on channel & pitch
      if (channel === 9 || channel === 10) {
        // Percussion / Drum channel
        osc.type = 'triangle';
        osc.frequency.setValueAtTime(freq * 0.5, now);
        osc.frequency.exponentialRampToValueAtTime(30, now + 0.12);
        gainNode.gain.setValueAtTime(velFactor * 0.9, now);
        gainNode.gain.exponentialRampToValueAtTime(0.001, now + 0.18);
        osc.stop(now + 0.2);
      } else if (channel === 1 || note < 48) {
        // Bass / Sub-bass
        osc.type = 'triangle';
        osc.frequency.setValueAtTime(freq, now);
        gainNode.gain.setValueAtTime(0.001, now);
        gainNode.gain.linearRampToValueAtTime(velFactor * 0.7, now + 0.02);
        gainNode.gain.exponentialRampToValueAtTime(velFactor * 0.45, now + 0.4);
      } else if (program >= 80 && program <= 95) {
        // Lead Synth / Square
        osc.type = 'sawtooth';
        osc.frequency.setValueAtTime(freq, now);
        gainNode.gain.setValueAtTime(0.001, now);
        gainNode.gain.linearRampToValueAtTime(velFactor * 0.5, now + 0.01);
        gainNode.gain.exponentialRampToValueAtTime(velFactor * 0.3, now + 0.35);
      } else {
        // Standard Piano / Chimes / Melodic Voice
        // Blend triangle + soft harmonic
        osc.type = 'triangle';
        osc.frequency.setValueAtTime(freq, now);
        gainNode.gain.setValueAtTime(0.001, now);
        gainNode.gain.linearRampToValueAtTime(velFactor * 0.65, now + 0.015);
        gainNode.gain.exponentialRampToValueAtTime(velFactor * 0.35, now + 0.5);
      }

      osc.connect(gainNode);
      gainNode.connect(this.masterGain);
      osc.start(now);

      this.activeVoices.set(key, { osc, gainNode, startTime: now });
    }

    noteOff(channel, note) {
      const key = `${channel}_${note}`;
      const voice = this.activeVoices.get(key);
      if (!voice) return;

      const now = this.ctx.currentTime;
      const { osc, gainNode } = voice;
      try {
        gainNode.gain.cancelScheduledValues(now);
        gainNode.gain.setValueAtTime(gainNode.gain.value, now);
        gainNode.gain.exponentialRampToValueAtTime(0.0001, now + 0.08);
        osc.stop(now + 0.1);
      } catch (e) {
        // Safe catch for already closed nodes
      }
      this.activeVoices.delete(key);
    }

    allNotesOff() {
      for (const [key, voice] of this.activeVoices.entries()) {
        try {
          voice.osc.stop();
          voice.osc.disconnect();
          voice.gainNode.disconnect();
        } catch (e) {}
      }
      this.activeVoices.clear();
    }
  }

  // Master MIDI Player Class
  class MidiPlayer {
    constructor() {
      this.audioCtx = null;
      this.synth = null;
      this.midiData = null;
      this.isPlaying = false;
      this.isPaused = false;
      this.currentTime = 0;
      this.playbackRate = 1.0;
      this.volume = 0.85;
      this.currentEventIndex = 0;
      this.timerId = null;
      this.lastTickTime = 0;
      this.onProgress = null; // (currTime, duration, percent) => {}
      this.onNote = null;     // (noteInfo) => {}
      this.onEnded = null;    // () => {}
      this.programsByChannel = {};
      this.activeNotesSet = new Set();
    }

    initAudio() {
      if (!this.audioCtx) {
        const AudioCtxClass = window.AudioContext || window.webkitAudioContext;
        this.audioCtx = new AudioCtxClass();
        this.synth = new MidiSynthesizer(this.audioCtx);
        this.synth.setVolume(this.volume);
      }
      if (this.audioCtx.state === 'suspended') {
        this.audioCtx.resume();
      }
    }

    async load(arrayBufferOrUrl) {
      let buffer = null;
      if (typeof arrayBufferOrUrl === 'string') {
        const res = await fetch(arrayBufferOrUrl);
        if (!res.ok) throw new Error(`HTTP ${res.status}: Failed to fetch MIDI file.`);
        buffer = await res.arrayBuffer();
      } else if (arrayBufferOrUrl instanceof ArrayBuffer) {
        buffer = arrayBufferOrUrl;
      } else if (arrayBufferOrUrl instanceof Blob) {
        buffer = await arrayBufferOrUrl.arrayBuffer();
      } else {
        throw new Error('Unsupported MIDI data source.');
      }

      this.stop();
      this.midiData = MidiParser.parse(buffer);
      this.currentTime = 0;
      this.currentEventIndex = 0;
      return this.midiData;
    }

    play() {
      if (!this.midiData) return;
      this.initAudio();

      if (this.isPaused) {
        this.isPaused = false;
        this.isPlaying = true;
        this.lastTickTime = performance.now();
        this.tick();
        return;
      }

      this.stop();
      this.isPlaying = true;
      this.isPaused = false;
      this.currentTime = 0;
      this.currentEventIndex = 0;
      this.programsByChannel = {};
      this.lastTickTime = performance.now();
      this.tick();
    }

    pause() {
      if (!this.isPlaying || this.isPaused) return;
      this.isPaused = true;
      this.isPlaying = false;
      if (this.timerId) {
        cancelAnimationFrame(this.timerId);
        this.timerId = null;
      }
      if (this.synth) this.synth.allNotesOff();
      this.activeNotesSet.clear();
      if (this.onNote) this.onNote({ activeNotes: [] });
    }

    resume() {
      if (this.isPaused) {
        this.play();
      }
    }

    stop() {
      this.isPlaying = false;
      this.isPaused = false;
      if (this.timerId) {
        cancelAnimationFrame(this.timerId);
        this.timerId = null;
      }
      if (this.synth) this.synth.allNotesOff();
      this.activeNotesSet.clear();
      this.currentTime = 0;
      this.currentEventIndex = 0;
      if (this.onProgress && this.midiData) {
        this.onProgress(0, this.midiData.duration, 0);
      }
      if (this.onNote) this.onNote({ activeNotes: [] });
    }

    seek(targetSeconds) {
      if (!this.midiData) return;
      const wasPlaying = this.isPlaying;
      if (this.synth) this.synth.allNotesOff();
      this.activeNotesSet.clear();

      targetSeconds = Math.max(0, Math.min(this.midiData.duration, targetSeconds));
      this.currentTime = targetSeconds;

      // Binary search / find new event index
      let newIdx = 0;
      while (newIdx < this.midiData.events.length && this.midiData.events[newIdx].time < targetSeconds) {
        const evt = this.midiData.events[newIdx];
        if (evt.type === 'programChange') {
          this.programsByChannel[evt.channel] = evt.program;
        }
        newIdx++;
      }
      this.currentEventIndex = newIdx;

      if (this.onProgress) {
        const percent = (this.currentTime / this.midiData.duration) * 100;
        this.onProgress(this.currentTime, this.midiData.duration, percent);
      }

      if (wasPlaying) {
        this.lastTickTime = performance.now();
      }
    }

    setPlaybackRate(rate) {
      this.playbackRate = Math.max(0.25, Math.min(3.0, rate));
    }

    setVolume(vol) {
      this.volume = Math.max(0, Math.min(1, vol));
      if (this.synth) this.synth.setVolume(this.volume);
    }

    tick() {
      if (!this.isPlaying) return;

      const now = performance.now();
      const deltaSec = ((now - this.lastTickTime) / 1000) * this.playbackRate;
      this.lastTickTime = now;
      this.currentTime += deltaSec;

      // Process all events up to current time
      const events = this.midiData.events;
      while (this.currentEventIndex < events.length && events[this.currentEventIndex].time <= this.currentTime) {
        const evt = events[this.currentEventIndex];
        if (evt.type === 'programChange') {
          this.programsByChannel[evt.channel] = evt.program;
        } else if (evt.type === 'noteOn') {
          const prog = this.programsByChannel[evt.channel] || 0;
          if (this.synth) this.synth.noteOn(evt.channel, evt.note, evt.velocity, prog);
          this.activeNotesSet.add(evt.note);
        } else if (evt.type === 'noteOff') {
          if (this.synth) this.synth.noteOff(evt.channel, evt.note);
          this.activeNotesSet.delete(evt.note);
        }
        this.currentEventIndex++;
      }

      if (this.onProgress && this.midiData) {
        const duration = this.midiData.duration;
        const percent = Math.min(100, (this.currentTime / duration) * 100);
        this.onProgress(this.currentTime, duration, percent);
      }

      if (this.onNote) {
        this.onNote({ activeNotes: Array.from(this.activeNotesSet) });
      }

      // Check for song completion
      if (this.currentTime >= this.midiData.duration || this.currentEventIndex >= events.length) {
        this.stop();
        if (this.onEnded) this.onEnded();
        return;
      }

      this.timerId = requestAnimationFrame(() => this.tick());
    }

    attachVisualizer(canvasElement) {
      if (!canvasElement) return null;
      const ctx = canvasElement.getContext('2d');

      let visualizerId = null;
      const draw = () => {
        const width = canvasElement.width = canvasElement.clientWidth || 300;
        const height = canvasElement.height = canvasElement.clientHeight || 80;

        ctx.clearRect(0, 0, width, height);

        // Background subtle grid
        ctx.fillStyle = 'rgba(15, 23, 42, 0.4)';
        ctx.fillRect(0, 0, width, height);

        const numBars = 32;
        const barWidth = (width / numBars) - 2;
        const activeNotes = Array.from(this.activeNotesSet);

        for (let i = 0; i < numBars; i++) {
          const x = i * (barWidth + 2) + 1;
          const noteRangeMin = 36 + Math.floor((i / numBars) * 50);
          const noteRangeMax = noteRangeMin + 2;
          const isActive = activeNotes.some(n => n >= noteRangeMin && n <= noteRangeMax);

          let barHeight = 6;
          if (isActive && this.isPlaying) {
            barHeight = height * 0.85;
          } else if (this.isPlaying) {
            barHeight = 6 + Math.sin((performance.now() / 150) + i) * 8;
          }

          const grad = ctx.createLinearGradient(0, height, 0, 0);
          if (isActive) {
            grad.addColorStop(0, '#4361ee');
            grad.addColorStop(1, '#4cc9f0');
          } else {
            grad.addColorStop(0, 'rgba(67, 97, 238, 0.3)');
            grad.addColorStop(1, 'rgba(76, 201, 240, 0.15)');
          }

          ctx.fillStyle = grad;
          ctx.beginPath();
          ctx.roundRect ? ctx.roundRect(x, height - barHeight, barWidth, barHeight, 3) : ctx.fillRect(x, height - barHeight, barWidth, barHeight);
          ctx.fill();
        }

        visualizerId = requestAnimationFrame(draw);
      };

      draw();

      return {
        stop: () => {
          if (visualizerId) cancelAnimationFrame(visualizerId);
        }
      };
    }
  }

  // Export globally
  global.MidiParser = MidiParser;
  global.MidiSynthesizer = MidiSynthesizer;
  global.MidiPlayer = MidiPlayer;
  global.midiPlayer = new MidiPlayer();

})(typeof window !== 'undefined' ? window : this);
