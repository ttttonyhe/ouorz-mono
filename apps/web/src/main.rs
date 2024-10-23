use leptos::*;

mod _app;

fn main() {
    // Enable debugging in the browser console
    _ = console_log::init_with_level(log::Level::Debug);
    console_error_panic_hook::set_once();

    // Mount the app
    mount_to_body(|| {
        view! { <_app::App /> }
    });
}
