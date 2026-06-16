<?php session_start(); require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Services – NexaBank</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="page-wrapper">
  <div class="container">
    <div class="page-header mt-lg text-center">
      <h2>Our <span class="text-gold">Services</span></h2>
      <p style="margin-top:8px;max-width:500px;margin-left:auto;margin-right:auto;">Comprehensive financial products designed for every stage of your life</p>
    </div>

    <div class="grid-3 mt-lg" style="grid-template-columns:repeat(auto-fit,minmax(280px,1fr));">

      <?php
      $services = [
        ['🏠','Home Loan','Finance your dream home with competitive rates and flexible repayment.','6.75% p.a.'],
        ['🎓','Education Loan','Invest in your future with affordable education financing.','8.75% p.a.'],
        ['🏥','Medical Loan','Emergency healthcare financing when you need it most.','10.75% p.a.'],
        ['🚗','Vehicle Loan','Drive home your dream vehicle with easy EMI options.','7.75% p.a.'],
        ['💼','Business Loan','Fuel your business growth with flexible capital.','6.75% p.a.'],
        ['👤','Personal Loan','Quick funds for any personal need, no collateral required.','9.75% p.a.'],
        ['🪙','Gold Loan','Leverage your gold assets for immediate liquidity.','6.75% p.a.'],
        ['💳','Credit Card','Earn rewards on every purchase with zero annual fee.','Free'],
      ];
      foreach ($services as [$icon, $name, $desc, $rate]):
      ?>
      <div class="service-card">
        <div class="icon"><?= $icon ?></div>
        <h3><?= $name ?></h3>
        <p><?= $desc ?></p>
        <div class="rate"><?= $rate ?></div>
        <a href="login.php" class="btn btn-outline btn-sm" style="margin-top:16px;">Apply Now →</a>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- CTA -->
    <div style="text-align:center;padding:60px 20px 0;">
      <h3>Ready to apply?</h3>
      <p style="margin-top:8px;margin-bottom:24px;color:var(--slate-400);">Open your NexaBank account first, then apply for any of our loan products.</p>
      <a href="createuser.php" class="btn btn-primary btn-lg">Open Account Free</a>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
