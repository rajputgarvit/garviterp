<?php
// Default Meta Values
$metaTitle = isset($pageTitle) ? $pageTitle . " - " . APP_NAME : APP_NAME . " | Enterprise ERP for Modern Business";
$metaDesc = isset($pageDescription) ? $pageDescription : "Acculynce is the leading cloud-based ERP for growing businesses. Manage inventory, finance, HR, and sales in one unified platform. Trusted by innovative companies visible worldwide.";
$metaImage = isset($pageImage) ? $pageImage : BASE_URL . "public/assets/images/og-image.jpg"; // Placeholder
$metaUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$metaKeywords = isset($pageKeywords) ? $pageKeywords : "ERP, Business Management, Inventory, Payroll, Finance, SaaS, Enterprise Software, B2B";

?>

<!-- Primary Meta Tags -->
<title><?php echo htmlspecialchars($metaTitle); ?></title>
<meta name="title" content="<?php echo htmlspecialchars($metaTitle); ?>">
<meta name="description" content="<?php echo htmlspecialchars($metaDesc); ?>">
<meta name="keywords" content="<?php echo htmlspecialchars($metaKeywords); ?>">
<meta name="author" content="Acculynce Inc.">
<meta name="robots" content="index, follow">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="canonical" href="<?php echo htmlspecialchars($metaUrl); ?>">

<!-- Open Graph / Facebook -->
<meta property="og:type" content="website">
<meta property="og:url" content="<?php echo htmlspecialchars($metaUrl); ?>">
<meta property="og:title" content="<?php echo htmlspecialchars($metaTitle); ?>">
<meta property="og:description" content="<?php echo htmlspecialchars($metaDesc); ?>">
<meta property="og:image" content="<?php echo htmlspecialchars($metaImage); ?>">

<!-- Twitter -->
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="<?php echo htmlspecialchars($metaUrl); ?>">
<meta property="twitter:title" content="<?php echo htmlspecialchars($metaTitle); ?>">
<meta property="twitter:description" content="<?php echo htmlspecialchars($metaDesc); ?>">
<meta property="twitter:image" content="<?php echo htmlspecialchars($metaImage); ?>">

<!-- Favicon -->
<link rel="icon" type="image/x-icon" href="<?php echo BASE_URL; ?>favicon.ico">

<!-- Structured Data (Organization) -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Organization",
  "name": "Acculynce",
  "url": "<?php echo BASE_URL; ?>",
  "logo": "<?php echo BASE_URL; ?>public/assets/images/logo.svg",
  "sameAs": [
    "https://twitter.com/acculynce",
    "https://linkedin.com/company/acculynce",
    "https://github.com/acculynce"
  ],
  "contactPoint": {
    "@type": "ContactPoint",
    "telephone": "+1-800-555-0123",
    "contactType": "customer service",
    "areaServed": "Global",
    "availableLanguage": "English"
  }
}
</script>
