import UIKit
import WebKit
import Network
import UniformTypeIdentifiers
import ObjectiveC

final class WebViewController: UIViewController {

    private let homeURL = URL(
        string: "https://keducation.site/index.php?source=ios&app_version=1.0.0"
    )!

    private let trustedHosts: Set<String> = [
        "keducation.site",
        "www.keducation.site"
    ]

    private var webView: WKWebView!
    private var progressObservation: NSKeyValueObservation?
    private var pendingDeepLink: URL?
    private var downloadDestination: URL?

    private let progressView = UIProgressView(progressViewStyle: .bar)
    private let offlineView = OfflineView()
    private let monitor = NWPathMonitor()
    private let monitorQueue = DispatchQueue(
        label: "site.keducation.network"
    )

    override func viewDidLoad() {
        super.viewDidLoad()

        view.backgroundColor = UIColor(
            red: 0.035,
            green: 0.055,
            blue: 0.15,
            alpha: 1
        )

        configureWebView()
        configureLayout()
        configureToolbar()
        configureNetwork()

        load(pendingDeepLink ?? homeURL)
    }

    deinit {
        progressObservation?.invalidate()
        monitor.cancel()
    }

    // MARK: - WebView Configuration

    private func configureWebView() {
        let configuration = WKWebViewConfiguration()

        configuration.websiteDataStore = .default()
        configuration.allowsInlineMediaPlayback = true
        configuration.mediaTypesRequiringUserActionForPlayback = []
        configuration.applicationNameForUserAgent = "KEducationiOS/1.0.0"
        configuration.preferences.javaScriptCanOpenWindowsAutomatically = false

        webView = WKWebView(
            frame: .zero,
            configuration: configuration
        )

        webView.translatesAutoresizingMaskIntoConstraints = false
        webView.navigationDelegate = self
        webView.uiDelegate = self

        webView.allowsBackForwardNavigationGestures = true
        webView.scrollView.keyboardDismissMode = .interactive

        webView.isOpaque = false
        webView.backgroundColor = view.backgroundColor

        progressObservation = webView.observe(
            \.estimatedProgress,
            options: [.new]
        ) { [weak self] webView, _ in

            DispatchQueue.main.async {
                self?.progressView.progress = Float(
                    webView.estimatedProgress
                )

                self?.progressView.isHidden =
                    webView.estimatedProgress >= 1
            }
        }
    }

    // MARK: - Layout

    private func configureLayout() {
        progressView.translatesAutoresizingMaskIntoConstraints = false

        progressView.progressTintColor = UIColor(
            red: 1,
            green: 0.61,
            blue: 0.15,
            alpha: 1
        )

        progressView.trackTintColor = .clear

        offlineView.translatesAutoresizingMaskIntoConstraints = false

        offlineView.retryButton.addTarget(
            self,
            action: #selector(retryConnection),
            for: .touchUpInside
        )

        view.addSubview(webView)
        view.addSubview(progressView)
        view.addSubview(offlineView)

        NSLayoutConstraint.activate([
            webView.topAnchor.constraint(
                equalTo: view.safeAreaLayoutGuide.topAnchor
            ),
            webView.leadingAnchor.constraint(
                equalTo: view.leadingAnchor
            ),
            webView.trailingAnchor.constraint(
                equalTo: view.trailingAnchor
            ),
            webView.bottomAnchor.constraint(
                equalTo: view.safeAreaLayoutGuide.bottomAnchor,
                constant: -50
            ),

            progressView.topAnchor.constraint(
                equalTo: webView.topAnchor
            ),
            progressView.leadingAnchor.constraint(
                equalTo: view.leadingAnchor
            ),
            progressView.trailingAnchor.constraint(
                equalTo: view.trailingAnchor
            ),
            progressView.heightAnchor.constraint(
                equalToConstant: 3
            ),

            offlineView.topAnchor.constraint(
                equalTo: webView.topAnchor
            ),
            offlineView.leadingAnchor.constraint(
                equalTo: view.leadingAnchor
            ),
            offlineView.trailingAnchor.constraint(
                equalTo: view.trailingAnchor
            ),
            offlineView.bottomAnchor.constraint(
                equalTo: view.safeAreaLayoutGuide.bottomAnchor
            )
        ])

        offlineView.isHidden = true
    }

    // MARK: - Toolbar

    private func configureToolbar() {
        let toolbar = UIToolbar()

        let space = UIBarButtonItem(
            barButtonSystemItem: .flexibleSpace,
            target: nil,
            action: nil
        )

        let space2 = UIBarButtonItem(
            barButtonSystemItem: .flexibleSpace,
            target: nil,
            action: nil
        )

        toolbar.translatesAutoresizingMaskIntoConstraints = false
        toolbar.isTranslucent = true

        toolbar.items = [
            UIBarButtonItem(
                image: UIImage(systemName: "chevron.backward"),
                style: .plain,
                target: self,
                action: #selector(goBack)
            ),
            UIBarButtonItem(
                image: UIImage(systemName: "chevron.forward"),
                style: .plain,
                target: self,
                action: #selector(goForward)
            ),
            space,
            UIBarButtonItem(
                image: UIImage(systemName: "house.fill"),
                style: .plain,
                target: self,
                action: #selector(goHome)
            ),
            space2,
            UIBarButtonItem(
                image: UIImage(systemName: "square.and.arrow.up"),
                style: .plain,
                target: self,
                action: #selector(sharePage)
            )
        ]

        view.addSubview(toolbar)

        NSLayoutConstraint.activate([
            toolbar.leadingAnchor.constraint(
                equalTo: view.leadingAnchor
            ),
            toolbar.trailingAnchor.constraint(
                equalTo: view.trailingAnchor
            ),
            toolbar.bottomAnchor.constraint(
                equalTo: view.safeAreaLayoutGuide.bottomAnchor
            ),
            toolbar.heightAnchor.constraint(
                equalToConstant: 50
            )
        ])
    }

    // MARK: - Network Monitoring

    private func configureNetwork() {
        monitor.pathUpdateHandler = { [weak self] path in
            DispatchQueue.main.async {
                guard let self = self else {
                    return
                }

                let offline = path.status != .satisfied
                self.offlineView.isHidden = !offline

                if !offline && self.webView.url == nil {
                    self.load(self.pendingDeepLink ?? self.homeURL)
                }
            }
        }

        monitor.start(queue: monitorQueue)
    }

    // MARK: - Navigation

    private func load(_ url: URL) {
        guard isTrusted(url) else {
            return
        }

        pendingDeepLink = nil

        webView.load(
            URLRequest(
                url: url,
                cachePolicy: .useProtocolCachePolicy,
                timeoutInterval: 30
            )
        )
    }

    func openDeepLink(_ url: URL) {
        var resolved = url

        if url.scheme == "keducation" {
            var components = URLComponents(
                url: url,
                resolvingAgainstBaseURL: false
            )

            components?.scheme = "https"
            components?.host = "keducation.site"

            if let value = components?.url {
                resolved = value
            }
        }

        guard isTrusted(resolved) else {
            return
        }

        if isViewLoaded {
            load(resolved)
        } else {
            pendingDeepLink = resolved
        }
    }

    private func isTrusted(_ url: URL) -> Bool {
        return url.scheme?.lowercased() == "https" &&
            trustedHosts.contains(
                url.host?.lowercased() ?? ""
            )
    }

    private func openExternal(_ url: URL) {
        UIApplication.shared.open(url)
    }

    // MARK: - Toolbar Actions

    @objc private func goBack() {
        if webView.canGoBack {
            webView.goBack()
        } else {
            load(homeURL)
        }
    }

    @objc private func goForward() {
        if webView.canGoForward {
            webView.goForward()
        }
    }

    @objc private func goHome() {
        load(homeURL)
    }

    @objc private func retryConnection() {
        offlineView.isHidden = true
        webView.reload()
    }

    @objc private func sharePage(_ sender: UIBarButtonItem) {
        guard let url = webView.url else {
            return
        }

        let activity = UIActivityViewController(
            activityItems: [url],
            applicationActivities: nil
        )

        activity.popoverPresentationController?.barButtonItem = sender

        present(activity, animated: true)
    }

    fileprivate func shareDownloadedFile(_ url: URL) {
        let activity = UIActivityViewController(
            activityItems: [url],
            applicationActivities: nil
        )

        activity.popoverPresentationController?.sourceView = view
        activity.popoverPresentationController?.sourceRect = CGRect(
            x: view.bounds.midX,
            y: view.bounds.maxY - 60,
            width: 1,
            height: 1
        )

        present(activity, animated: true)
    }
}

// MARK: - Navigation Delegate

extension WebViewController: WKNavigationDelegate {

    func webView(
        _ webView: WKWebView,
        decidePolicyFor action: WKNavigationAction,
        decisionHandler: @escaping (WKNavigationActionPolicy) -> Void
    ) {
        guard let url = action.request.url else {
            decisionHandler(.cancel)
            return
        }

        if isTrusted(url) {
            decisionHandler(.allow)
            return
        }

        if ["tel", "mailto", "sms", "https"].contains(
            url.scheme?.lowercased() ?? ""
        ) {
            openExternal(url)
        }

        decisionHandler(.cancel)
    }

    func webView(
        _ webView: WKWebView,
        decidePolicyFor response: WKNavigationResponse,
        decisionHandler: @escaping (WKNavigationResponsePolicy) -> Void
    ) {
        decisionHandler(
            !response.canShowMIMEType ? .download : .allow
        )
    }

    func webView(
        _ webView: WKWebView,
        navigationAction: WKNavigationAction,
        didBecome download: WKDownload
    ) {
        download.delegate = self
    }

    func webView(
        _ webView: WKWebView,
        navigationResponse: WKNavigationResponse,
        didBecome download: WKDownload
    ) {
        download.delegate = self
    }

    func webView(
        _ webView: WKWebView,
        didFail navigation: WKNavigation!,
        withError error: Error
    ) {
        show(error)
    }

    func webView(
        _ webView: WKWebView,
        didFailProvisionalNavigation navigation: WKNavigation!,
        withError error: Error
    ) {
        show(error)
    }

    private func show(_ error: Error) {
        if (error as NSError).code != NSURLErrorCancelled {
            offlineView.isHidden = false
        }
    }
}

// MARK: - WebView UI Delegate

extension WebViewController: WKUIDelegate {

    func webView(
        _ webView: WKWebView,
        createWebViewWith configuration: WKWebViewConfiguration,
        for action: WKNavigationAction,
        windowFeatures: WKWindowFeatures
    ) -> WKWebView? {

        if let url = action.request.url {
            if isTrusted(url) {
                load(url)
            } else {
                openExternal(url)
            }
        }

        return nil
    }

    func webView(
        _ webView: WKWebView,
        runJavaScriptAlertPanelWithMessage message: String,
        initiatedByFrame frame: WKFrameInfo,
        completionHandler: @escaping () -> Void
    ) {
        let alert = UIAlertController(
            title: "K Education",
            message: message,
            preferredStyle: .alert
        )

        alert.addAction(
            UIAlertAction(title: "OK", style: .default) { _ in
                completionHandler()
            }
        )

        present(alert, animated: true)
    }

    func webView(
        _ webView: WKWebView,
        runJavaScriptConfirmPanelWithMessage message: String,
        initiatedByFrame frame: WKFrameInfo,
        completionHandler: @escaping (Bool) -> Void
    ) {
        let alert = UIAlertController(
            title: "K Education",
            message: message,
            preferredStyle: .alert
        )

        alert.addAction(
            UIAlertAction(title: "Cancel", style: .cancel) { _ in
                completionHandler(false)
            }
        )

        alert.addAction(
            UIAlertAction(title: "Continue", style: .default) { _ in
                completionHandler(true)
            }
        )

        present(alert, animated: true)
    }

    // FIX: Availability annotation for iOS 18.4+

    @available(iOS 18.4, *)
    func webView(
        _ webView: WKWebView,
        runOpenPanelWith parameters: WKOpenPanelParameters,
        initiatedByFrame frame: WKFrameInfo,
        completionHandler: @escaping ([URL]?) -> Void
    ) {
        let picker = UIDocumentPickerViewController(
            forOpeningContentTypes: [.image, .pdf, .data],
            asCopy: true
        )

        let delegate = FilePickerDelegate(
            completion: completionHandler
        )

        picker.allowsMultipleSelection = parameters.allowsMultipleSelection
        picker.delegate = delegate

        objc_setAssociatedObject(
            picker,
            &AssociatedKeys.filePicker,
            delegate,
            .OBJC_ASSOCIATION_RETAIN_NONATOMIC
        )

        present(picker, animated: true)
    }
}

// MARK: - Download Delegate

extension WebViewController: WKDownloadDelegate {

    func download(
        _ download: WKDownload,
        decideDestinationUsing response: URLResponse,
        suggestedFilename: String,
        completionHandler: @escaping (URL?) -> Void
    ) {
        let url = FileManager.default.temporaryDirectory
            .appendingPathComponent(
                suggestedFilename.replacingOccurrences(
                    of: "/",
                    with: "-"
                )
            )

        try? FileManager.default.removeItem(at: url)

        downloadDestination = url
        completionHandler(url)
    }

    func downloadDidFinish(_ download: WKDownload) {
        guard let url = downloadDestination else {
            return
        }

        DispatchQueue.main.async {
            self.shareDownloadedFile(url)
        }
    }
}

// MARK: - File Picker Delegate

private final class FilePickerDelegate: NSObject, UIDocumentPickerDelegate {

    private let completion: ([URL]?) -> Void

    init(completion: @escaping ([URL]?) -> Void) {
        self.completion = completion
    }

    func documentPicker(
        _ controller: UIDocumentPickerViewController,
        didPickDocumentsAt urls: [URL]
    ) {
        completion(urls)
    }

    func documentPickerWasCancelled(
        _ controller: UIDocumentPickerViewController
    ) {
        completion(nil)
    }
}

private enum AssociatedKeys {
    static var filePicker: UInt8 = 0
}

// MARK: - Offline Screen

private final class OfflineView: UIView {

    let retryButton = UIButton(type: .system)

    override init(frame: CGRect) {
        super.init(frame: frame)

        backgroundColor = UIColor(
            red: 0.035,
            green: 0.055,
            blue: 0.15,
            alpha: 0.98
        )

        let icon = UIImageView(
            image: UIImage(systemName: "wifi.slash")
        )

        icon.tintColor = .white
        icon.contentMode = .scaleAspectFit

        let title = UILabel()
        title.text = "You’re offline"
        title.textColor = .white
        title.font = .boldSystemFont(ofSize: 26)
        title.textAlignment = .center

        let message = UILabel()
        message.text = "Reconnect to continue your K Education journey."
        message.textColor = UIColor.white.withAlphaComponent(0.72)
        message.numberOfLines = 0
        message.textAlignment = .center

        retryButton.setTitle("Try again", for: .normal)
        retryButton.titleLabel?.font = .boldSystemFont(ofSize: 16)

        retryButton.backgroundColor = UIColor(
            red: 1,
            green: 0.61,
            blue: 0.15,
            alpha: 1
        )

        retryButton.tintColor = UIColor(
            red: 0.08,
            green: 0.07,
            blue: 0.2,
            alpha: 1
        )

        retryButton.layer.cornerRadius = 14

        retryButton.heightAnchor.constraint(
            equalToConstant: 50
        ).isActive = true

        let stack = UIStackView(
            arrangedSubviews: [
                icon,
                title,
                message,
                retryButton
            ]
        )

        stack.axis = .vertical
        stack.spacing = 18
        stack.translatesAutoresizingMaskIntoConstraints = false

        addSubview(stack)

        NSLayoutConstraint.activate([
            stack.centerYAnchor.constraint(
                equalTo: centerYAnchor
            ),
            stack.leadingAnchor.constraint(
                equalTo: leadingAnchor,
                constant: 35
            ),
            stack.trailingAnchor.constraint(
                equalTo: trailingAnchor,
                constant: -35
            ),
            icon.heightAnchor.constraint(
                equalToConstant: 58
            )
        ])
    }

    required init?(coder: NSCoder) {
        fatalError("init(coder:) has not been implemented")
    }
}