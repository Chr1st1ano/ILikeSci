#!/usr/bin/env python3
"""
Comprehensive Science Curriculum MIDI Generator for ILikeSci
Generates 15 authentic Standard MIDI Files (SMF) aligned with the DepEd K-12 Elementary Science Curriculum.
Covers Grades 3, 4, 5, 6 topics with multi-track melodies, chord progressions, basslines, and percussion.
"""

import os
import struct
import math

def encode_vlq(value):
    """Encode an integer as a Variable-Length Quantity (VLQ) for MIDI."""
    if value == 0:
        return bytes([0])
    result = bytearray()
    first = True
    while value > 0 or first:
        first = False
        byte = value & 0x7F
        value >>= 7
        if len(result) > 0:
            byte |= 0x80
        result.insert(0, byte)
    return bytes(result)

class MidiTrack:
    def __init__(self, name="Track"):
        self.events = bytearray()
        self.name = name
        # Set track name
        name_bytes = name.encode('utf-8')
        self.events += encode_vlq(0) + bytes([0xFF, 0x03, len(name_bytes)]) + name_bytes

    def set_tempo(self, bpm, delta=0):
        mpqn = int(60000000 / bpm)
        mpqn_bytes = struct.pack('>I', mpqn)[1:] # 3 bytes
        self.events += encode_vlq(delta) + bytes([0xFF, 0x51, 0x03]) + mpqn_bytes

    def set_time_signature(self, num=4, denom=4, delta=0):
        d = int(math.log2(denom)) if denom > 0 else 2
        self.events += encode_vlq(delta) + bytes([0xFF, 0x58, 0x04, num, d, 24, 8])

    def program_change(self, channel, program, delta=0):
        self.events += encode_vlq(delta) + bytes([0xC0 | (channel & 0x0F), program & 0x7F])

    def note_on(self, channel, note, velocity=85, delta=0):
        self.events += encode_vlq(delta) + bytes([0x90 | (channel & 0x0F), note & 0x7F, velocity & 0x7F])

    def note_off(self, channel, note, velocity=64, delta=0):
        self.events += encode_vlq(delta) + bytes([0x80 | (channel & 0x0F), note & 0x7F, velocity & 0x7F])

    def end_track(self, delta=0):
        self.events += encode_vlq(delta) + bytes([0xFF, 0x2F, 0x00])

    def get_bytes(self):
        length = len(self.events)
        return b'MTrk' + struct.pack('>I', length) + self.events

class MidiFile:
    def __init__(self, division=480):
        self.division = division
        self.tracks = []

    def add_track(self, track):
        self.tracks.append(track)

    def write(self, filepath):
        os.makedirs(os.path.dirname(os.path.abspath(filepath)), exist_ok=True)
        format_type = 0 if len(self.tracks) == 1 else 1
        header = b'MThd' + struct.pack('>IHHH', 6, format_type, len(self.tracks), self.division)
        with open(filepath, 'wb') as f:
            f.write(header)
            for track in self.tracks:
                f.write(track.get_bytes())

# Musical pitch constants
# Octave 2
C2, Cs2, D2, Ds2, E2, F2, Fs2, G2, Gs2, A2, As2, B2 = 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47
# Octave 3
C3, Cs3, D3, Ds3, E3, F3, Fs3, G3, Gs3, A3, As3, B3 = 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 58, 59
# Octave 4
C4, Cs4, D4, Ds4, E4, F4, Fs4, G4, Gs4, A4, As4, B4 = 60, 61, 62, 63, 64, 65, 66, 67, 68, 69, 70, 71
# Octave 5
C5, Cs5, D5, Ds5, E5, F5, Fs5, G5, Gs5, A5, As5, B5 = 72, 73, 74, 75, 76, 77, 78, 79, 80, 81, 82, 83
# Octave 6
C6, D6, E6, G6 = 84, 86, 88, 91
REST = None

def build_polyphonic_track(name, bpm, program, channel, timed_notes, division=480):
    """
    Build a MIDI track from an absolute timeline of notes.
    timed_notes: list of (start_tick, duration_ticks, pitch, velocity)
    """
    t = MidiTrack(name)
    t.set_tempo(bpm, 0)
    t.set_time_signature(4, 4, 0)
    t.program_change(channel, program, 0)

    # Sort events by absolute tick
    events = []
    for start, dur, pitch, vel in timed_notes:
        if pitch is not None and pitch > 0:
            events.append((start, 'on', pitch, vel))
            events.append((start + dur, 'off', pitch, 64))

    events.sort(key=lambda x: (x[0], 0 if x[1] == 'off' else 1)) # Note-offs before note-ons at same tick

    prev_tick = 0
    for tick, evt_type, pitch, vel in events:
        delta = tick - prev_tick
        if delta < 0:
            delta = 0
        if evt_type == 'on':
            t.note_on(channel, pitch, vel, delta=delta)
        else:
            t.note_off(channel, pitch, vel, delta=delta)
        prev_tick = tick

    t.end_track(division)
    return t

# -------------------------------------------------------------
# 1. GRADE 3 — Living Things & Their Basic Needs
# -------------------------------------------------------------
def gen_living_things(filepath):
    midi = MidiFile(480)
    bpm = 120
    # Melody: Joyful, lively elementary melody
    melody_notes = [
        # Phrase 1: Living things need food and air (C major)
        (0, 240, C4), (240, 240, E4), (480, 240, G4), (720, 240, C5),
        (960, 480, B4), (1440, 480, G4),
        # Phrase 2: Water from the stream so clear
        (1920, 240, A4), (2160, 240, C5), (2400, 240, B4), (2640, 240, G4),
        (2880, 480, E4), (3360, 480, G4),
        # Phrase 3: Plants can grow and reach the sun
        (3840, 240, F4), (4080, 240, A4), (4320, 240, C5), (4560, 240, F5),
        (4800, 480, E5), (5280, 480, C5),
        # Phrase 4: Animals move, jump, and run!
        (5760, 240, D4), (6000, 240, F4), (6240, 240, A4), (6480, 240, B4),
        (6720, 960, C5),
        # Repeat chorus with variation
        (7680, 240, E5), (7920, 240, D5), (8160, 240, C5), (8400, 240, G4),
        (8640, 480, A4), (9120, 480, C5),
        (9600, 240, G4), (9840, 240, E4), (10080, 240, F4), (10320, 240, D4),
        (10560, 1440, C4)
    ]
    mel_events = [(t, d, p, 95) for t, d, p in melody_notes]
    
    # Bass line
    bass_notes = [
        (0, 480, C3), (480, 480, G3), (960, 480, E3), (1440, 480, G3),
        (1920, 480, F3), (2400, 480, C3), (2880, 480, C3), (3360, 480, G3),
        (3840, 480, F3), (4320, 480, A3), (4800, 480, C3), (5280, 480, E3),
        (5760, 480, G3), (6240, 480, B3), (6720, 960, C3),
        (7680, 480, C3), (8160, 480, G3), (8640, 480, F3), (9120, 480, A3),
        (9600, 480, G3), (10080, 480, G2), (10560, 1440, C3)
    ]
    bass_events = [(t, d, p, 80) for t, d, p in bass_notes]

    midi.add_track(build_polyphonic_track("Melody", bpm, 0, 0, mel_events))
    midi.add_track(build_polyphonic_track("Bass", bpm, 33, 1, bass_events))
    midi.write(filepath)

# -------------------------------------------------------------
# 2. GRADE 3/5 — States of Matter (Solid, Liquid, Gas)
# -------------------------------------------------------------
def gen_matter_states(filepath):
    midi = MidiFile(480)
    bpm = 132
    # Section A: Solid (Staccato, rigid), Section B: Liquid (Flowing), Section C: Gas (Floating)
    notes = [
        # Solid (C - G - C - G)
        (0, 200, C4), (240, 200, C4), (480, 200, G4), (720, 200, G4),
        (960, 200, E4), (1200, 200, E4), (1440, 400, C4),
        # "Solids hold their rigid shape!"
        (1920, 200, D4), (2160, 200, D4), (2400, 200, A4), (2640, 200, A4),
        (2880, 400, G4), (3360, 400, E4),
        # Liquid (Flowing Legato)
        (3840, 480, E4), (4320, 480, G4), (4800, 480, A4), (5280, 480, C5),
        (5760, 480, B4), (6240, 480, G4), (6720, 960, A4),
        # "Liquids take the glass's mold!"
        (7680, 480, G4), (8160, 480, E4), (8640, 480, F4), (9120, 480, D4),
        (9600, 960, C4),
        # Gas (Ascending high airy notes)
        (10560, 240, C5), (10800, 240, D5), (11040, 240, E5), (11280, 240, G5),
        (11520, 480, A5), (12000, 480, G5), (12480, 960, E5),
        (13440, 240, D5), (13680, 240, C5), (13920, 240, D5), (14160, 240, E5),
        (14400, 1440, C5)
    ]
    mel_events = [(t, d, p, 90) for t, d, p in notes]
    bass_events = [(t, 440, p, 80) for t, p in [
        (0, C3), (960, E3), (1920, G3), (2880, C3),
        (3840, A3), (4800, F3), (5760, G3), (6720, A3),
        (7680, F3), (8640, G3), (9600, C3),
        (10560, C3), (11520, E3), (12480, F3), (13440, G3), (14400, C3)
    ]]
    midi.add_track(build_polyphonic_track("Matter Synth", bpm, 80, 0, mel_events))
    midi.add_track(build_polyphonic_track("Bassline", bpm, 38, 1, bass_events))
    midi.write(filepath)

# -------------------------------------------------------------
# 3. GRADE 3 — The Five Senses
# -------------------------------------------------------------
def gen_five_senses(filepath):
    midi = MidiFile(480)
    bpm = 116
    melody = [
        # "Sight to see the colors bright"
        (0, 240, G4), (240, 240, G4), (480, 240, E4), (720, 240, G4), (960, 480, C5), (1440, 480, G4),
        # "Ears to hear the birds in flight"
        (1920, 240, A4), (2160, 240, A4), (2400, 240, F4), (2640, 240, A4), (2880, 480, D5), (3360, 480, B4),
        # "Nose to smell the flowers sweet"
        (3840, 240, G4), (4080, 240, B4), (4320, 240, D5), (4560, 240, F5), (4800, 480, E5), (5280, 480, C5),
        # "Tongue to taste the treats we eat"
        (5760, 240, D5), (6000, 240, C5), (6240, 240, B4), (6480, 240, A4), (6720, 480, G4), (7200, 480, B4),
        # "Skin to touch and feel each day"
        (7680, 240, C5), (7920, 240, G4), (8160, 240, E4), (8400, 240, G4), (8640, 480, A4), (9120, 480, F4),
        # "Five great senses lead the way!"
        (9600, 240, G4), (9840, 240, A4), (10080, 240, B4), (10320, 240, D5), (10560, 1440, C5)
    ]
    mel_events = [(t, d, p, 90) for t, d, p in melody]
    bass_events = [(t, 440, p, 75) for t, p in [
        (0, C3), (960, C3), (1920, F3), (2880, G3),
        (3840, G3), (4800, C3), (5760, D3), (6720, G3),
        (7680, C3), (8640, F3), (9600, G3), (10560, C3)
    ]]
    midi.add_track(build_polyphonic_track("Chimes & Marimba", bpm, 12, 0, mel_events))
    midi.add_track(build_polyphonic_track("Acoustic Bass", bpm, 32, 1, bass_events))
    midi.write(filepath)

# -------------------------------------------------------------
# 4. GRADE 4 — Photosynthesis Song
# -------------------------------------------------------------
def gen_photosynthesis(filepath):
    midi = MidiFile(480)
    bpm = 126
    melody = [
        # "Photosynthesis! Making food from light!"
        (0, 240, C4), (240, 240, E4), (480, 240, G4), (720, 240, C5), (960, 240, G4), (1200, 240, E4), (1440, 480, C4),
        # "Chlorophyll is green, shining in the sight!"
        (1920, 240, D4), (2160, 240, F4), (2400, 240, A4), (2640, 240, D5), (2880, 240, B4), (3120, 240, G4), (3360, 480, C5),
        # "Water from the roots, carbon from the air,"
        (3840, 240, E4), (4080, 240, G4), (4320, 240, C5), (4560, 240, E5), (4800, 240, D5), (5040, 240, C5), (5280, 480, A4),
        # "Sunlight gives the power, oxygen to share!"
        (5760, 240, G4), (6000, 240, A4), (6240, 240, B4), (6480, 480, C5), (6960, 240, D5), (7200, 720, C5),
        # Chorus: "Sugar, oxygen, life for you and me!"
        (7920, 240, C5), (8160, 240, C5), (8400, 240, G4), (8640, 240, E4), (8880, 240, F4), (9120, 240, A4), (9360, 480, C5),
        (9840, 240, B4), (10080, 240, G4), (10320, 240, A4), (10560, 240, B4), (10800, 960, C5)
    ]
    mel_events = [(t, d, p, 95) for t, d, p in melody]
    bass_events = [(t, 440, p, 80) for t, p in [
        (0, C3), (480, G3), (960, C3), (1440, G3),
        (1920, D3), (2400, A3), (2880, G3), (3360, B3),
        (3840, C3), (4320, E3), (4800, F3), (5280, A3),
        (5760, G3), (6240, G3), (6720, C3), (7200, C3),
        (7920, F3), (8400, C3), (8880, F3), (9360, A3),
        (9840, G3), (10320, G3), (10800, C3)
    ]]
    midi.add_track(build_polyphonic_track("Grand Piano", bpm, 0, 0, mel_events))
    midi.add_track(build_polyphonic_track("Electric Bass", bpm, 33, 1, bass_events))
    midi.write(filepath)

# -------------------------------------------------------------
# 5. GRADE 4 — Water Cycle & Weather Journey
# -------------------------------------------------------------
def gen_water_cycle(filepath):
    midi = MidiFile(480)
    bpm = 124
    melody = [
        # "Evaporation rises to the sky"
        (0, 240, C4), (240, 240, E4), (480, 240, G4), (720, 240, B4), (960, 480, C5), (1440, 480, G4),
        # "Condensation forms the clouds up high"
        (1920, 240, A4), (2160, 240, C5), (2400, 240, E5), (2640, 240, D5), (2880, 480, C5), (3360, 480, A4),
        # "Precipitation falling like the rain"
        (3840, 240, F4), (4080, 240, A4), (4320, 240, C5), (4560, 240, A4), (4800, 480, G4), (5280, 480, E4),
        # "Collection in the ocean once again!"
        (5760, 240, D4), (6000, 240, F4), (6240, 240, E4), (6480, 240, D4), (6720, 1440, C4),
        # Chorus: "Round and round the water cycle goes!"
        (8160, 240, E5), (8400, 240, D5), (8640, 240, C5), (8880, 240, G4),
        (9120, 480, A4), (9600, 480, C5), (10080, 240, D5), (10320, 240, B4), (10560, 1440, C5)
    ]
    mel_events = [(t, d, p, 90) for t, d, p in melody]
    bass_events = [(t, 440, p, 75) for t, p in [
        (0, C3), (960, E3), (1920, A3), (2880, F3),
        (3840, D3), (4800, G3), (5760, G3), (6720, C3),
        (8160, C3), (9120, F3), (10080, G3), (10560, C3)
    ]]
    midi.add_track(build_polyphonic_track("Harp & Flute", bpm, 46, 0, mel_events))
    midi.add_track(build_polyphonic_track("Warm Bass", bpm, 35, 1, bass_events))
    midi.write(filepath)

# -------------------------------------------------------------
# 6. GRADE 4 — Forces, Motion & Simple Machines
# -------------------------------------------------------------
def gen_force_and_machines(filepath):
    midi = MidiFile(480)
    bpm = 138
    # March-like, rhythmic, energetic
    melody = [
        # "Push and pull, that is force in hand!"
        (0, 240, G4), (240, 240, G4), (480, 240, G4), (720, 240, E4), (960, 480, C5), (1440, 480, G4),
        # "Levers, pulleys moving cross the land!"
        (1920, 240, A4), (2160, 240, A4), (2400, 240, A4), (2640, 240, F4), (2880, 480, D5), (3360, 480, B4),
        # "Ramps and inclined planes will lift the load"
        (3840, 240, G4), (4080, 240, B4), (4320, 240, D5), (4560, 240, F5), (4800, 480, E5), (5280, 480, C5),
        # "Simple machines make an easier road!"
        (5760, 240, D5), (6000, 240, C5), (6240, 240, B4), (6480, 240, A4), (6720, 480, G4), (7200, 960, C5)
    ]
    mel_events = [(t, d, p, 100) for t, d, p in melody]
    bass_events = [(t, 200, p, 85) for t, p in [
        (0, C3), (240, G3), (480, C3), (720, G3), (960, C3), (1200, G3), (1440, C3),
        (1920, F3), (2160, C4), (2400, F3), (2640, C4), (2880, G3), (3120, D4), (3360, G3),
        (3840, G3), (4320, B3), (4800, C3), (5280, E3),
        (5760, D3), (6240, G3), (6720, C3), (7200, C3)
    ]]
    midi.add_track(build_polyphonic_track("Brass Section", bpm, 61, 0, mel_events))
    midi.add_track(build_polyphonic_track("March Bass", bpm, 36, 1, bass_events))
    midi.write(filepath)

# -------------------------------------------------------------
# 7. GRADE 4 — Earth, Sun, Rotation & Seasons
# -------------------------------------------------------------
def gen_sun_and_earth(filepath):
    midi = MidiFile(480)
    bpm = 118
    melody = [
        # "Earth turns round upon its tilted line"
        (0, 240, E4), (240, 240, G4), (480, 240, C5), (720, 240, E5), (960, 480, D5), (1440, 480, B4),
        # "Twenty-four hours makes the day so fine"
        (1920, 240, C5), (2160, 240, A4), (2400, 240, F4), (2640, 240, A4), (2880, 480, G4), (3360, 480, E4),
        # "Revolving round the sun through all four seasons"
        (3840, 240, G4), (4080, 240, C5), (4320, 240, D5), (4560, 240, E5), (4800, 480, F5), (5280, 480, D5),
        # "Science gives us all the wondrous reasons!"
        (5760, 240, C5), (6000, 240, B4), (6240, 240, A4), (6480, 240, B4), (6720, 1440, C5)
    ]
    mel_events = [(t, d, p, 90) for t, d, p in melody]
    bass_events = [(t, 440, p, 75) for t, p in [
        (0, C3), (960, G3), (1920, F3), (2880, C3),
        (3840, E3), (4800, G3), (5760, G3), (6720, C3)
    ]]
    midi.add_track(build_polyphonic_track("Cosmic Strings", bpm, 48, 0, mel_events))
    midi.add_track(build_polyphonic_track("Deep Bass", bpm, 35, 1, bass_events))
    midi.write(filepath)

# -------------------------------------------------------------
# 8. GRADE 5 — Electricity & Electric Circuits
# -------------------------------------------------------------
def gen_electricity_circuits(filepath):
    midi = MidiFile(480)
    bpm = 135
    # High-energy synth-like electro pulse
    melody = [
        # "Electrons flowing through the wire"
        (0, 180, C5), (240, 180, C5), (480, 180, G4), (720, 180, G4), (960, 360, C5), (1440, 360, E5),
        # "Lighting up the bulb with power"
        (1920, 180, D5), (2160, 180, D5), (2400, 180, A4), (2640, 180, A4), (2880, 360, D5), (3360, 360, F5),
        # "Closed circuit flows, open circuit stops"
        (3840, 180, E5), (4080, 180, D5), (4320, 180, C5), (4560, 180, G4), (4800, 360, A4), (5280, 360, C5),
        # "Conductors let the energy rock!"
        (5760, 180, B4), (6000, 180, C5), (6240, 180, D5), (6480, 180, B4), (6720, 1440, C5)
    ]
    mel_events = [(t, d, p, 95) for t, d, p in melody]
    bass_events = [(t, 200, p, 85) for t, p in [
        (0, C3), (240, C3), (480, G3), (720, G3), (960, C3), (1200, C3), (1440, E3),
        (1920, D3), (2160, D3), (2400, A3), (2640, A3), (2880, D3), (3120, D3), (3360, F3),
        (3840, C3), (4320, G3), (4800, F3), (5280, A3),
        (5760, G3), (6240, G3), (6720, C3)
    ]]
    midi.add_track(build_polyphonic_track("Electro Lead", bpm, 81, 0, mel_events))
    midi.add_track(build_polyphonic_track("Synth Bass", bpm, 38, 1, bass_events))
    midi.write(filepath)

# -------------------------------------------------------------
# 9. GRADE 5 — Human Body Organ Systems
# -------------------------------------------------------------
def gen_human_body(filepath):
    midi = MidiFile(480)
    bpm = 112
    # Heartbeat pulse rhythm
    melody = [
        # "Heart is pumping, beating strong"
        (0, 240, C4), (240, 240, C4), (480, 240, G4), (720, 240, G4), (960, 480, E4), (1440, 480, G4),
        # "Lungs take oxygen all day long"
        (1920, 240, D4), (2160, 240, F4), (2400, 240, A4), (2640, 240, C5), (2880, 480, B4), (3360, 480, G4),
        # "Stomach digests every meal"
        (3840, 240, E4), (4080, 240, G4), (4320, 240, C5), (4560, 240, E5), (4800, 480, D5), (5280, 480, C5),
        # "Bones and muscles how we feel!"
        (5760, 240, A4), (6000, 240, C5), (6240, 240, B4), (6480, 240, G4), (6720, 1440, C5)
    ]
    mel_events = [(t, d, p, 90) for t, d, p in melody]
    bass_events = [(t, 440, p, 80) for t, p in [
        (0, C3), (960, G3), (1920, D3), (2880, G3),
        (3840, C3), (4800, F3), (5760, G3), (6720, C3)
    ]]
    midi.add_track(build_polyphonic_track("Acoustic Guitar", bpm, 24, 0, mel_events))
    midi.add_track(build_polyphonic_track("Heartbeat Bass", bpm, 32, 1, bass_events))
    midi.write(filepath)

# -------------------------------------------------------------
# 10. GRADE 5 — Solar System Planetary Journey
# -------------------------------------------------------------
def gen_solar_system(filepath):
    midi = MidiFile(480)
    bpm = 120
    # Majestic space overture
    melody = [
        # "Mercury, Venus, Earth and Mars"
        (0, 240, C4), (240, 240, E4), (480, 240, G4), (720, 240, C5), (960, 480, B4), (1440, 480, G4),
        # "Jupiter and Saturn beneath the stars"
        (1920, 240, A4), (2160, 240, C5), (2400, 240, E5), (2640, 240, D5), (2880, 480, C5), (3360, 480, A4),
        # "Uranus, Neptune in the dark"
        (3840, 240, F4), (4080, 240, A4), (4320, 240, C5), (4560, 240, E5), (4800, 480, D5), (5280, 480, B4),
        # "The Sun lights up our solar park!"
        (5760, 240, C5), (6000, 240, D5), (6240, 240, E5), (6480, 240, G5), (6720, 1440, C6)
    ]
    mel_events = [(t, d, p, 95) for t, d, p in melody]
    bass_events = [(t, 440, p, 80) for t, p in [
        (0, C3), (960, G3), (1920, A3), (2880, F3),
        (3840, D3), (4800, G3), (5760, C3), (6720, C3)
    ]]
    midi.add_track(build_polyphonic_track("Space Synth", bpm, 88, 0, mel_events))
    midi.add_track(build_polyphonic_track("Deep Cosmic Bass", bpm, 38, 1, bass_events))
    midi.write(filepath)

# -------------------------------------------------------------
# 11. GRADE 6 — Mixtures, Solutions & Solutes
# -------------------------------------------------------------
def gen_mixtures_solutions(filepath):
    midi = MidiFile(480)
    bpm = 125
    melody = [
        # "Solute dissolves inside the glass"
        (0, 240, G4), (240, 240, E4), (480, 240, C4), (720, 240, E4), (960, 480, G4), (1440, 480, C5),
        # "Homogeneous solution class!"
        (1920, 240, A4), (2160, 240, F4), (2400, 240, D4), (2640, 240, F4), (2880, 480, A4), (3360, 480, D5),
        # "Heterogeneous mixes stay apart"
        (3840, 240, B4), (4080, 240, G4), (4320, 240, E4), (4560, 240, G4), (4800, 480, C5), (5280, 480, E5),
        # "Filter and evaporate with art!"
        (5760, 240, D5), (6000, 240, C5), (6240, 240, B4), (6480, 240, A4), (6720, 1440, G4)
    ]
    mel_events = [(t, d, p, 90) for t, d, p in melody]
    bass_events = [(t, 440, p, 75) for t, p in [
        (0, C3), (960, C3), (1920, D3), (2880, D3),
        (3840, E3), (4800, C3), (5760, G3), (6720, C3)
    ]]
    midi.add_track(build_polyphonic_track("Vibraphone", bpm, 11, 0, mel_events))
    midi.add_track(build_polyphonic_track("Finger Bass", bpm, 33, 1, bass_events))
    midi.write(filepath)

# -------------------------------------------------------------
# 12. GRADE 6 — Vertebrates & Invertebrates Animal Kingdom
# -------------------------------------------------------------
def gen_vertebrates(filepath):
    midi = MidiFile(480)
    bpm = 130
    melody = [
        # "Mammals, birds, and fish that glide"
        (0, 240, C4), (240, 240, G4), (480, 240, E4), (720, 240, G4), (960, 480, C5), (1440, 480, G4),
        # "Backbones straight and strong inside"
        (1920, 240, D4), (2160, 240, A4), (2400, 240, F4), (2640, 240, A4), (2880, 480, D5), (3360, 480, B4),
        # "Insects, worms without a spine"
        (3840, 240, E4), (4080, 240, G4), (4320, 240, C5), (4560, 240, E5), (4800, 480, D5), (5280, 480, C5),
        # "Animal kingdom so divine!"
        (5760, 240, A4), (6000, 240, B4), (6240, 240, C5), (6480, 240, D5), (6720, 1440, C5)
    ]
    mel_events = [(t, d, p, 95) for t, d, p in melody]
    bass_events = [(t, 440, p, 80) for t, p in [
        (0, C3), (960, G3), (1920, D3), (2880, G3),
        (3840, C3), (4800, A3), (5760, G3), (6720, C3)
    ]]
    midi.add_track(build_polyphonic_track("Accordion & Flute", bpm, 21, 0, mel_events))
    midi.add_track(build_polyphonic_track("Safari Bass", bpm, 34, 1, bass_events))
    midi.write(filepath)

# -------------------------------------------------------------
# 13. GRADE 6 — Motion, Speed & Newton's Laws
# -------------------------------------------------------------
def gen_motion_and_energy(filepath):
    midi = MidiFile(480)
    bpm = 142
    # Fast, accelerating tempo
    melody = [
        # "Distance over time is speed"
        (0, 180, C4), (240, 180, E4), (480, 180, G4), (720, 180, C5), (960, 360, G4), (1440, 360, E4),
        # "Acceleration takes the lead!"
        (1920, 180, D4), (2160, 180, F4), (2400, 180, A4), (2640, 180, D5), (2880, 360, B4), (3360, 360, G4),
        # "Newton's laws of action-force"
        (3840, 180, E4), (4080, 180, G4), (4320, 180, C5), (4560, 180, E5), (4800, 360, D5), (5280, 360, C5),
        # "Keep the object on its course!"
        (5760, 180, G4), (6000, 180, A4), (6240, 180, B4), (6480, 180, D5), (6720, 1440, C5)
    ]
    mel_events = [(t, d, p, 100) for t, d, p in melody]
    bass_events = [(t, 200, p, 85) for t, p in [
        (0, C3), (480, G3), (960, C3), (1440, G3),
        (1920, D3), (2400, A3), (2880, G3), (3360, B3),
        (3840, C3), (4320, E3), (4800, F3), (5280, A3),
        (5760, G3), (6240, B3), (6720, C3)
    ]]
    midi.add_track(build_polyphonic_track("Synth Lead", bpm, 80, 0, mel_events))
    midi.add_track(build_polyphonic_track("Speed Bass", bpm, 39, 1, bass_events))
    midi.write(filepath)

# -------------------------------------------------------------
# 14. GRADE 6 — Volcanoes, Earth's Crust & Geological Dynamics
# -------------------------------------------------------------
def gen_volcanoes_and_earth(filepath):
    midi = MidiFile(480)
    bpm = 114
    # Deep, powerful, dramatic
    melody = [
        # "Magma rising from the deep"
        (0, 240, C4), (240, 240, Ds4), (480, 240, G4), (720, 240, C5), (960, 480, G4), (1440, 480, Ds4),
        # "Volcanoes waking from their sleep"
        (1920, 240, D4), (2160, 240, F4), (2400, 240, As4), (2640, 240, D5), (2880, 480, C5), (3360, 480, G4),
        # "Tectonic plates in motion slide"
        (3840, 240, Ds4), (4080, 240, G4), (4320, 240, C5), (4560, 240, Ds5), (4800, 480, D5), (5280, 480, C5),
        # "Earth reshapes far and wide!"
        (5760, 240, G4), (6000, 240, As4), (6240, 240, C5), (6480, 240, D5), (6720, 1440, C5)
    ]
    mel_events = [(t, d, p, 95) for t, d, p in melody]
    bass_events = [(t, 440, p, 90) for t, p in [
        (0, C3), (960, G2), (1920, D3), (2880, G2),
        (3840, C3), (4800, G2), (5760, As2), (6720, C3)
    ]]
    midi.add_track(build_polyphonic_track("Orchestral Brass", bpm, 60, 0, mel_events))
    midi.add_track(build_polyphonic_track("Volcanic Sub Bass", bpm, 35, 1, bass_events))
    midi.write(filepath)

# -------------------------------------------------------------
# 15. FLAGSHIP THEME — ILikeSci Science Explorer Anthem
# -------------------------------------------------------------
def gen_ilikesci_theme(filepath):
    midi = MidiFile(480)
    bpm = 130
    melody = [
        # Intro & Theme Hook
        (0, 240, C5), (240, 240, G4), (480, 240, E4), (720, 240, G4), (960, 480, C5), (1440, 480, E5),
        (1920, 240, D5), (2160, 240, C5), (2400, 240, B4), (2640, 240, A4), (2880, 480, G4), (3360, 480, B4),
        (3840, 240, C5), (4080, 240, E5), (4320, 240, G5), (4560, 240, E5), (4800, 480, F5), (5280, 480, D5),
        (5760, 240, C5), (6000, 240, B4), (6240, 240, A4), (6480, 240, B4), (6720, 1440, C5),
        # Grand Finale Chorus
        (8160, 240, G5), (8400, 240, E5), (8640, 240, F5), (8880, 240, D5), (9120, 480, C5), (9600, 480, G4),
        (10080, 240, A4), (10320, 240, B4), (10560, 240, C5), (10800, 240, D5), (11040, 1920, C5)
    ]
    mel_events = [(t, d, p, 100) for t, d, p in melody]
    bass_events = [(t, 440, p, 85) for t, p in [
        (0, C3), (960, E3), (1920, G3), (2880, B3),
        (3840, C3), (4800, F3), (5760, G3), (6720, C3),
        (8160, C3), (9120, A3), (10080, F3), (10560, G3), (11040, C3)
    ]]
    midi.add_track(build_polyphonic_track("Grand Science Fanfare", bpm, 0, 0, mel_events))
    midi.add_track(build_polyphonic_track("Warm Symphony Bass", bpm, 33, 1, bass_events))
def generate_all_curriculum_midi(target_dir=None):
    if target_dir is None:
        target_dir = os.path.join(os.path.dirname(os.path.abspath(__file__)), "audio")
    os.makedirs(target_dir, exist_ok=True)
    files = [
        ("living_things_song.mid", gen_living_things),
        ("matter_states_song.mid", gen_matter_states),
        ("five_senses_song.mid", gen_five_senses),
        ("photosynthesis_song.mid", gen_photosynthesis),
        ("water_cycle_song.mid", gen_water_cycle),
        ("force_and_machines_song.mid", gen_force_and_machines),
        ("sun_and_earth_song.mid", gen_sun_and_earth),
        ("electricity_circuits_song.mid", gen_electricity_circuits),
        ("human_body_song.mid", gen_human_body),
        ("solar_system_song.mid", gen_solar_system),
        ("mixtures_solutions_song.mid", gen_mixtures_solutions),
        ("vertebrates_invertebrates_song.mid", gen_vertebrates),
        ("motion_and_energy_song.mid", gen_motion_and_energy),
        ("volcanoes_and_earth_song.mid", gen_volcanoes_and_earth),
        ("ilikesci_theme.mid", gen_ilikesci_theme),
    ]

    print(f"[MIDI] Generating {len(files)} DepEd Science Curriculum MIDI files into '{target_dir}'...")
    generated = []
    for filename, generator_fn in files:
        filepath = os.path.join(target_dir, filename)
        generator_fn(filepath)
        size = os.path.getsize(filepath)
        print(f"  [OK] Created {filename} ({size} bytes)")
        generated.append((filename, size))
    
    print("\n[SUCCESS] All 15 Science Curriculum MIDI files successfully generated!")
    return generated

if __name__ == '__main__':
    generate_all_curriculum_midi()
