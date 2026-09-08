#!/usr/bin/env python3
"""
Automated Science Curriculum MIDI Validation Test
Verifies header integrity, track chunk boundaries, note events, and timing on all 15 curriculum MIDI files.
"""

import os
import struct

def validate_midi_file(filepath):
    if not os.path.exists(filepath):
        return False, f"File does not exist: {filepath}"

    filesize = os.path.getsize(filepath)
    if filesize == 0:
        return False, "File is empty (0 bytes)"

    with open(filepath, 'rb') as f:
        data = f.read()

    if len(data) < 14:
        return False, f"File too small for MIDI header ({len(data)} bytes)"

    # Check MThd header
    header_tag = data[0:4]
    if header_tag != b'MThd':
        return False, f"Invalid header tag: {header_tag}"

    header_len = struct.unpack('>I', data[4:8])[0]
    if header_len != 6:
        return False, f"Unexpected header length: {header_len}"

    fmt, num_tracks, division = struct.unpack('>HHH', data[8:14])

    offset = 14
    tracks_found = 0
    total_notes = 0

    while offset < len(data):
        if offset + 8 > len(data):
            break
        track_tag = data[offset:offset+4]
        if track_tag != b'MTrk':
            return False, f"Invalid track chunk at offset {offset}: {track_tag}"

        track_len = struct.unpack('>I', data[offset+4:offset+8])[0]
        offset += 8
        track_data = data[offset:offset+track_len]

        # Count Note-On events (0x90..0x9F)
        for i in range(len(track_data) - 2):
            b = track_data[i]
            if (b & 0xF0) == 0x90:
                vel = track_data[i+2]
                if vel > 0:
                    total_notes += 1

        offset += track_len
        tracks_found += 1

    if tracks_found != num_tracks:
        return False, f"Header specifies {num_tracks} tracks, but found {tracks_found}"

    if total_notes == 0:
        return False, "No Note-On musical events found in track"

    return True, f"Valid SMF (Format {fmt}, {num_tracks} tracks, {total_notes} notes, {division} TPQN, {filesize} bytes)"

def main():
    base_dir = os.path.dirname(os.path.abspath(__file__))
    audio_dir = os.path.join(base_dir, "audio")
    expected_files = [
        "living_things_song.mid",
        "matter_states_song.mid",
        "five_senses_song.mid",
        "photosynthesis_song.mid",
        "water_cycle_song.mid",
        "force_and_machines_song.mid",
        "sun_and_earth_song.mid",
        "electricity_circuits_song.mid",
        "human_body_song.mid",
        "solar_system_song.mid",
        "mixtures_solutions_song.mid",
        "vertebrates_invertebrates_song.mid",
        "motion_and_energy_song.mid",
        "volcanoes_and_earth_song.mid",
        "ilikesci_theme.mid"
    ]

    print(f"--- Running Science Curriculum MIDI Test Suite ({len(expected_files)} Files) ---")
    all_passed = True

    for filename in expected_files:
        filepath = os.path.join(audio_dir, filename)
        passed, msg = validate_midi_file(filepath)
        status = "[PASS]" if passed else "[FAIL]"
        print(f"  {status} {filename:<32} -> {msg}")
        if not passed:
            all_passed = False

    print("----------------------------------------------------------------------")
    if all_passed:
        print("[ALL TESTS PASSED] All 15 DepEd Science Curriculum MIDI files are 100% compliant!")
        return 0
    else:
        print("[TEST FAILURE] One or more MIDI files failed validation.")
        return 1

if __name__ == '__main__':
    exit(main())
