// Suppress the console window on Windows in release builds.
// Without this attribute, Windows would open a terminal window alongside the app.
#![cfg_attr(not(debug_assertions), windows_subsystem = "windows")]

fn main() {
    medismart_desktop::run();
}
