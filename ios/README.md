# K Education for iOS

This folder contains the native iPhone/iPad app for K Education.

- Xcode project: `KEducation.xcodeproj`
- Scheme: `KEducation`
- Bundle identifier: `site.keducation.app`
- Minimum deployment target: iOS 15
- Production service: `https://keducation.site`
- Codemagic configuration: repository root `codemagic.yaml`
- Publishing instructions: `CODEMAGIC-STEPS.md`
- App Store copy and privacy checklist: `APP-STORE-LISTING.md`

The app uses Apple's `WKWebView` with persistent login cookies, native back/forward/home/share controls, secure external-link handling, file uploads, downloads, deep links, safe-area support, network monitoring and a native offline state.

No Apple credentials or signing certificates belong in this repository. Codemagic obtains signing files using the private App Store Connect integration configured in the Codemagic dashboard.
