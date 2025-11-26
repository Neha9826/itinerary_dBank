<?php
session_start();
if (isset($_SESSION['user_id'])) { header("Location: dashboard.php"); exit(); }
?>
<!doctype html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Login | Itinerary Data Bank</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" />
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" />
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" />
    
    <link rel="stylesheet" href="./css/adminlte.css" />
  </head>
  
  <body class="login-page bg-body-secondary">
    <div class="login-box">
      <div class="card card-outline card-primary">
        <div class="card-header">
          <a href="#" class="link-dark text-center link-offset-2 link-opacity-100 link-opacity-50-hover">
            <h1 class="mb-0"><b>Itinerary</b>DataBank</h1>
          </a>
        </div>
        <div class="card-body login-card-body">
          <p class="login-box-msg">Sign in to start your session</p>

          <form action="actions/login_action.php" method="post">
            
            <div class="input-group mb-1">
              <div class="form-floating">
                <input id="loginEmail" name="email" type="email" class="form-control" placeholder="" required />
                <label for="loginEmail">Email</label>
              </div>
              <div class="input-group-text">
                <span class="bi bi-envelope"></span>
              </div>
            </div>

            <div class="input-group mb-1">
              <div class="form-floating">
                <input id="loginPassword" name="password" type="password" class="form-control" placeholder="" required />
                <label for="loginPassword">Password</label>
              </div>
              <div class="input-group-text">
                <span class="bi bi-lock-fill"></span>
              </div>
            </div>

            <div class="row mt-3">
              <div class="col-8 d-inline-flex align-items-center">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault" />
                  <label class="form-check-label" for="flexCheckDefault"> Remember Me </label>
                </div>
              </div>
              <div class="col-4">
                <div class="d-grid gap-2">
                  <button type="submit" class="btn btn-primary">Sign In</button>
                </div>
              </div>
            </div>
          </form>
          <p class="mb-0 mt-3">
            <a href="register.php" class="text-center"> Register a new membership </a>
          </p>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"></script>
    <script src="./js/adminlte.js"></script>
  </body>
</html>