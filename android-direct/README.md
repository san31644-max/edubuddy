# K Education Android wrapper

This dependency-free Android wrapper builds the direct-download APK and opens
the production K Education service at `https://keducation.site`.

Run `build.ps1` for the direct-download APK. Run `build-aab.ps1` for the signed
Google Play bundle at `build/K-Education-1.0.3.aab` (target API 36, version code 4).

Release signing credentials are stored outside Git in `android-direct/signing/`.
Keep that folder backed up: every Android update must use the same key.
