export {};

declare global {
  interface Window {
    bpKnowledgeTestsAdmin?: {
      restUrl: string;
      restNonce: string;
      pluginVersion: string;
      adminUrls?: {
        list: string;
        addTest: string;
        settings: string;
      };
    };
  }
}
