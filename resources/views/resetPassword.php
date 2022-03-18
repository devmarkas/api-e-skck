<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>E - SKCK reset password</title>

    <!-- Custom fonts for this template-->
    <link href="template/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="template/css/sb-admin-2.min.css" rel="stylesheet">
    <link href="template/css/login.css" rel="stylesheet">



</head>

<body class="bg-white">

    <div class="o-hidden border-0">
        <div class="full">
            <div class="row full">
                <div class="icon"></div>
                <div class="col-lg-6 d-flex align-items-center">
                    <div class="p-5 form1">
                        <div class="text-center">
                            <div class="head-title">Reset Password</div>
                            <div class="title mb-4">Enter your email and we'll send you an email with instruction to
                                reset your password.</div>
                        </div>
                        <?php if ($error != null) : ?>
                            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2" viewBox="0 0 16 16" role="img" aria-label="Warning:">
                                    <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z" />
                                </svg>
                                <div>
                                    <?php foreach ($error as $message) : ?>
                                        <?php echo $message ?>
                                    <?php endforeach ?>
                                </div>
                            </div>
                        <?php endif ?>
                        <form method="POST" action="/save-new-password">
                            <input type="hidden" name="expires" value="<?php echo $request->expires ?>">
                            <input type="hidden" name="signature" value="<?php echo $request->signature ?>">

                            <div class="form-group ">
                                <label for="exampleFormControlInput1">Password</label>
                                <input type="password" name="password" class="input">
                            </div>
                            <div class="form-group ">
                                <label for="exampleFormControlInput1">Password Confirmation</label>
                                <input type="password" name="password_confirmation" class="input">

                            </div>

                            <div class="d-flex justify-content-center">
                                <button type="submit" class="btn login a-login mt-4">
                                    Reset
                                </button>
                            </div>
                        </form>


                    </div>
                </div>
                <div class="col-lg-6 d-none d-lg-block bg-login1"></div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="template/vendor/jquery/jquery.min.js"></script>
    <script src="template/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="template/vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="template/js/sb-admin-2.min.js"></script>

</body>

</html>