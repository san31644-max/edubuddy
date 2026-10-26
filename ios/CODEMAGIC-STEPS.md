# Publish K Education to TestFlight with Codemagic

The repository contains a native iPhone/iPad project at `ios/KEducation.xcodeproj`. Its bundle identifier is `site.keducation.app`. The production service is `https://keducation.site`.

## 1. Join Apple Developer

1. Sign in at https://developer.apple.com/account with the Apple Account that will own K Education.
2. Join the Apple Developer Program. Apple currently charges USD 99 per membership year.
3. If enrolling as a company, use the legal organization account that should appear as the App Store seller.

## 2. Register the app identifier

1. Open Apple Developer → Certificates, Identifiers & Profiles → Identifiers.
2. Press `+`, choose **App IDs**, then **App**.
3. Description: `K Education`.
4. Bundle ID type: **Explicit**.
5. Bundle ID: `site.keducation.app`.
6. Register it. No optional capability is required for the first build.

## 3. Create the App Store Connect app

1. Open https://appstoreconnect.apple.com.
2. Choose **Apps → + → New App**.
3. Platforms: **iOS**.
4. Name: `K Education`.
5. Primary language: choose English (U.K.) or your preferred listing language.
6. Bundle ID: select `site.keducation.app`.
7. SKU: `KEDUCATION-IOS-001`.
8. User access: **Full Access**.
9. Create the app record before running the signed Codemagic workflow.

## 4. Create the App Store Connect API key

1. In App Store Connect, open **Users and Access → Integrations → App Store Connect API**.
2. Press `+` to generate a key.
3. Name it `Codemagic K Education`.
4. Give it **App Manager** access.
5. Download the `.p8` private key immediately. Apple lets you download it only once.
6. Save the **Issuer ID** and **Key ID** shown on that page.

Never commit the `.p8` file, certificates, passwords, or API IDs to this repository.

## 5. Put the project in a private Git repository

Codemagic builds from GitHub, GitLab, or Bitbucket. Create a private repository and push this project only when you are ready. Confirm that private keys, SQL exports, runtime secrets, uploaded student files, and local session files are excluded before pushing.

The root of the repository must contain `codemagic.yaml`.

## 6. Connect Codemagic

1. Create an account at https://codemagic.io.
2. Choose **Add application**.
3. Connect the private repository and select it.
4. Select **Other / Native iOS** if Codemagic asks for a project type.
5. Finish adding the application.

## 7. Add the Apple integration

1. In Codemagic, open **Team settings → Integrations → Developer Portal**.
2. Add a new App Store Connect key.
3. Integration name must be exactly: `keducation_app_store`.
4. Enter the Apple **Issuer ID** and **Key ID**.
5. Upload the downloaded `.p8` key.
6. Save it.

The name must match `app_store_connect: keducation_app_store` in `codemagic.yaml`.

## 8. Run the first compile check

1. Open the app in Codemagic.
2. Choose **Start new build**.
3. Select the `ios-keducation-build-only` workflow.
4. Select the branch containing the iOS project.
5. Start the build.

This workflow compiles the app for an iOS Simulator without signing or uploading anything. Fix any compile failure before continuing.

## 9. Build and upload to TestFlight

1. Start another build.
2. Select `ios-keducation-testflight`.
3. Codemagic will create/fetch the Apple Distribution certificate and App Store provisioning profile, build the IPA, and upload it to TestFlight.
4. If Apple reports that automatic signing cannot create a profile, confirm that the App ID exists and the API key has App Manager access.
5. Wait for Apple to process the build. The build then appears in App Store Connect → K Education → TestFlight.

The workflow intentionally uploads to TestFlight but does not automatically submit the public App Store release.

## 10. Test on a real iPhone

1. In App Store Connect → TestFlight, add yourself as an internal tester.
2. Install Apple's TestFlight app on an iPhone.
3. Install K Education and verify login, Ayubowan audio, dashboard scrolling, AI tutor, lessons, quizzes, games, profile photo selection, downloads, account deletion, subscription access, and external links.
4. Test with slow or disconnected internet and verify the native offline screen.
5. Test both a free account and a Premium account.

## 11. Complete the App Store listing

Use `ios/APP-STORE-LISTING.md` for the prepared description, keywords, review notes, and privacy checklist. You must also provide real iPhone/iPad screenshots captured from the tested build.

Because the app requires sign-in, create a stable reviewer account with representative lessons and include its username and password under **App Review Information**. Do not use a real student's account.

## 12. Submit for review

1. Open App Store Connect → K Education → App Store.
2. Create version `1.0.0` if it is not already present.
3. Complete the description, keywords, support URL, privacy URL, category, age rating, content rights, App Privacy, screenshots, and review contact.
4. Select the processed TestFlight build.
5. Answer export compliance. `ITSAppUsesNonExemptEncryption` is already set to false because the app only relies on standard HTTPS supplied by Apple.
6. Add the demo login and review notes.
7. Choose manual release for the first version.
8. Press **Add for Review**, then **Submit for Review**.

Do not enable `submit_to_app_store: true` in Codemagic until the first release has passed real-device testing and all App Store metadata is complete.
