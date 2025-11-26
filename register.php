<?php
// Include DB connection if needed later, but for now just the View
?>
<!doctype html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Register | Itinerary Data Bank</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" />
    
    <link rel="stylesheet" href="./css/adminlte.css" />
  </head>
  
  <body class="register-page bg-body-secondary">
    <div class="register-box">
      <div class="card card-outline card-primary">
        <div class="card-header">
          <a href="#" class="link-dark text-center link-offset-2 link-opacity-100 link-opacity-50-hover">
            <h1 class="mb-0"><b>Itinerary</b>DataBank</h1>
          </a>
        </div>
        <div class="card-body register-card-body">
          <p class="register-box-msg">Register a new membership</p>

          <form action="actions/register_action.php" method="post">
            
            <div class="input-group mb-1">
              <div class="form-floating">
                <input id="registerFullName" name="name" type="text" class="form-control" placeholder="" required />
                <label for="registerFullName">Full Name</label>
              </div>
              <div class="input-group-text">
                <span class="bi bi-person"></span>
              </div>
            </div>

            <div class="input-group mb-1">
              <div class="form-floating">
                <input id="registerEmail" name="email" type="email" class="form-control" placeholder="" required />
                <label for="registerEmail">Email</label>
              </div>
              <div class="input-group-text">
                <span class="bi bi-envelope"></span>
              </div>
            </div>

            <div class="input-group mb-1">
              <div class="form-floating">
                <input id="registerPassword" name="password" type="password" class="form-control" placeholder="" required />
                <label for="registerPassword">Password</label>
              </div>
              <div class="input-group-text">
                <span class="bi bi-lock-fill"></span>
              </div>
            </div>

            <div class="input-group mb-1">
                <div class="form-floating">
                    <select name="role" class="form-select" id="roleSelect">
                        <option value="employee">Employee</option>
                        <option value="agent">Agent</option>
                        </select>
                    <label for="roleSelect">Register As</label>
                </div>
            </div>

            <div class="row mt-3">
              <div class="col-8 d-inline-flex align-items-center">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault" />
                  <label class="form-check-label" for="flexCheckDefault">
                    I agree to the <a href="#">terms</a>
                  </label>
                </div>
              </div>
              <div class="col-4">
                <div class="d-grid gap-2">
                  <button type="submit" class="btn btn-primary">Register</button>
                </div>
              </div>
            </div>
          </form>

          <p class="mb-0 mt-3">
            <a href="login.php" class="link-primary text-center"> I already have a membership </a>
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