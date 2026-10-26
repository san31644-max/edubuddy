import UIKit

final class SceneDelegate: UIResponder, UIWindowSceneDelegate {
    var window: UIWindow?
    func scene(_ scene: UIScene, willConnectTo session: UISceneSession, options connectionOptions: UIScene.ConnectionOptions) {
        guard let windowScene = scene as? UIWindowScene else { return }
        let controller = WebViewController(), navigation = UINavigationController()
        navigation.setViewControllers([controller], animated: false);navigation.setNavigationBarHidden(true, animated: false)
        let window = UIWindow(windowScene: windowScene);window.rootViewController = navigation;window.tintColor = UIColor(red: 0.11, green: 0.39, blue: 0.96, alpha: 1);window.makeKeyAndVisible();self.window = window
        if let context = connectionOptions.urlContexts.first { controller.openDeepLink(context.url) }
    }
    func scene(_ scene: UIScene, openURLContexts contexts: Set<UIOpenURLContext>) { if let url=contexts.first?.url { controller?.openDeepLink(url) } }
    func scene(_ scene: UIScene, continue activity: NSUserActivity) { if let url=activity.webpageURL { controller?.openDeepLink(url) } }
    private var controller: WebViewController? { (window?.rootViewController as? UINavigationController)?.viewControllers.first as? WebViewController }
}
