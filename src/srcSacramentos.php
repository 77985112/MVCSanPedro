<?php
session_start();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <title>San Pedro de Sacaba</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="shortcut icon" href="../assets/images/Icono.png">
    <link rel="stylesheet" type="text/css" href="../assets/vendor/font-awesome/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="../assets/vendor/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" type="text/css" href="../assets/vendor/tiny-slider/dist/tiny-slider.css">
    <link rel="stylesheet" type="text/css" href="../assets/vendor/plyr/plyr.css">
    <link rel="stylesheet" type="text/css" href="../assets/css/style.css">
    <link rel="stylesheet" type="text/css" href="../assets/css/Index.css">
    <style>
        .bg-mode {
            background-color: #ffffff;
            color: #000000;
        }

        .navbar-light .navbar-brand {
            color: #000000;
        }
    </style>

</head>

<body data-bs-theme="light">

    <header class="navbar-light fixed-top header-static bg-mode">
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="navbar-brand" href="../index.php">
                    <img class="light-mode-item navbar-brand-item" src="../assets/images/logo.png" alt="logo">
                    <img class="dark-mode-item navbar-brand-item" src="../assets/images/logo-light.png" alt="logo">
                </a>
                <button class="navbar-toggler ms-auto icon-md btn btn-light p-0" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false"
                    aria-label="Toggle navigation">
                    <span class="navbar-toggler-animation">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>

                <div class="collapse navbar-collapse" id="navbarCollapse">
                    <ul class="navbar-nav navbar-nav-scroll me-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="../index.php">Inicio</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="srcActividadGeneral.php">Actividades</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link active dropdown-toggle" href="#" id="SacramentoMenu"
                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Sacramentos</a>
                            <ul class="dropdown-menu" aria-labelledby="SacramentoMenu">
                                <li> <a class="dropdown-item" href="srcSacramentoBautizo.php">Bautizo</a></li>
                                <li> <a class="dropdown-item" href="srcSacramentoComunion.php">Primera Comunión</a></li>
                                <li> <a class="dropdown-item" href="srcSacramentoConfirmacion.php">Confirmación</a></li>
                                <li> <a class="dropdown-item" href="srcSacramentoMatrimonio.php">Matrimonio</a></li>
                                <li> <a class="dropdown-item active" href="srcSacramentos.php">Todos los sacramentos</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="NosotrosMenu" data-bs-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">Conócenos</a>
                            <ul class="dropdown-menu" aria-labelledby="NosotrosMenu">
                                <li> <a class="dropdown-item" href="srcNosotrosParroquia.php">Parroquia</a></li>
                                <li> <a class="dropdown-item" href="srcNosotrosComunion.php">Primera Comunión</a></li>
                                <li> <a class="dropdown-item" href="srcNosotrosConfirmacion.php">Confirmación</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
                <div class="ms-3 ms-lg-auto me-2" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Ingresar">
                    <button type="button" class="btn btn-primary-soft" data-bs-toggle="modal"
                        data-bs-target="#exampleModal">
                        <i class="bi bi-person-fill"></i>
                    </button>
                </div>
                <div class="ms-3 ms-lg-auto" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Contactos">
                    <a type="button" href="srcContacto.php" class="btn btn-primary-soft">
                        <i class="bi bi-chat-left-dots"></i>
                    </a>
                </div>
            </div>
        </nav>
    </header>


    <main>
        <div>
            <div class="container mt-5">
                <div class="col-lg-12 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h2 class="col-lg-12 text-center">¿Que son los sacramentos?</h2>
                            <p>Los <strong>siete sacramentos</strong> de la Iglesia católica son <strong>bautismo</strong>, <strong>eucaristía</strong>, <strong>confirmación</strong>, <strong>matrimonio</strong>, <strong>orden sacerdotal</strong>, <strong>reconciliación</strong> y <strong>unción de los enfermos</strong>. Estos sacramentos son reconocidos también por la Iglesia ortodoxa y la Iglesia copta.</p>
                            <p>Se entiende por sacramento un signo sensible y eficaz de la gracia divina, y un medio para alcanzar la santidad. Se dice que los sacramentos son eficaces, pues en ellos se hace realidad lo que significan. En efecto, para los creyentes, los sacramentos comunican la presencia real (pero invisible) de Dios a través de un signo visible.</p>
                            <p>Para la Iglesia, los sacramentos fueron instituidos por Jesucristo. Los argumentos se encuentran en diferentes pasajes de los evangelios y en las cartas de los Apóstoles, en la Biblia. A continuación, veamos en qué consiste cada sacramento, su significado, sus símbolos y fundamentos bíblicos.</p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">1. Bautismo</h5>
                                <img alt="7 sacramentos" src="../assets/images/post/Sacramentos/bautismo.jpg">
                                <p class="card-text">Bautismo de un bebé. La fotografía documenta el momento en que se derrama el agua sobre su cabeza, en señal de su nacimiento espiritual a la vida cristiana. </p>
                                <p>El bautismo es un sacramento de iniciación en el cual el contrayente recibe el Espíritu Santo, inicia el seguimiento del evangelio y se integra a la Iglesia. El ministro suele ser un obispo, sacerdote o diácono. Sin embargo, en caso de peligro de muerte, cualquier bautizado puede administrar un <em>bautismo de emergencia</em>. El bautismo está dirigido tanto a niños como a adultos.</p>
                                <h3>Significado del bautismo</h3>
                                <p>La ceremonia del bautismo representa la purificación del pecado original y convierte al bautizado en templo vivo de Dios. Por lo tanto, el bautismo constituye una invitación a la santidad y a la Iglesia, y el comienzo de una vida fundada en el Evangelio.</p>
                                <p><strong>El signo visible y obligatorio del bautismo es el agua</strong>, que representa la purificación y la renovación de la vida. La Iglesia practica el bautismo por ablución, que consiste en derramar agua sobre la cabeza del bautizando.</p>
                                <p>La oración o forma que sella la eficacia del bautismo es la siguiente: «Yo te bautizo en el nombre del Padre, del Hijo y del Espíritu Santo».</p>
                                <p>Existen otros elementos complementarios de la liturgia bautismal, tales como la <strong>unción del óleo y el Santo Crisma; la luz del cirio pascual y las vestiduras blancas</strong>. El óleo es aceite de oliva que se unge en el pecho para transmitir el don de fortaleza. El Santo Crisma es aceite perfumado que se unge en la cabeza en representación del Espíritu Santo.</p>
                                <p>La luz del cirio pascual es una vela larga y gruesa que representa a Cristo resucitado y el deber cristiano de irradiar su luz. En el bautismo, se transmite a <strong>padres y padrinos </strong>para que aumenten la fe del bautizado. Las vestiduras blancas son el símbolo de la santidad y de la entrada al «rebaño» de Cristo, o sea, a la Iglesia (por eso antiguamente se confeccionaba en lana de ovejas).</p>
                                <h3>Fundamentos bíblicos del bautismo</h3>
                                <p>El fundamento del bautismo está en los evangelios. Según estos, Juan el Bautista administraba el bautismo de inmersión en las aguas del río Jordán. Jesús fue bautizado por Juan antes de iniciar su vida pública (ver Mateo 3, 13-17; Marcos 1, 9-11; Lucas 3, 21-22; Juan 1,29-34).</p>
                                <p>Los evangelios señalan también que Jesús encomendó a los apóstoles bautizar: “Vayan, pues, y hagan discípulos a todas las gentes bautizándolas en el nombre del Padre y del Hijo y del Espíritu Santo” (Mateo 28, 19). Otras referencias son: Marcos 16, 16 y Juan 3, 5; los Hechos de los Apóstoles y las cartas pastorales de Pablo y Pedro.</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">2. Eucaristía</h5>
                                <img alt="7 sacramentos" src="../assets/images/post/Sacramentos/comunion.jpg">
                                <p class="card-text">Eucaristía. La fotografía documenta el momento en que el sacerdote se prepara para llevar a cabo la consagración del pan y el vino, los cuales se encuentran en el copón y el cáliz respectivamente. </p>
                                <p>La eucaristía consiste en el memorial de la vida, pasión, muerte y resurrección de Jesús, por medio del ofrecimiento (consagración) del pan y el vino a Dios. También recibe el nombre de comunión. Asimismo, la ceremonia en que se recibe el pan y el vino por primera vez se llama <strong>Primera Comunión</strong>. El ministro de la eucaristía es el sacerdote. El sacramento va dirigido a toda la comunidad de fieles. Los bautizados y preparados pueden consumir el pan en forma de hostia.</p>
                                <h3>Significado de la eucaristía</h3>
                                <p>La eucaristía es el sacramento por excelencia del catolicismo, ya que resume toda la fe cristiana. La eucaristía es el signo visible de la presencia de Jesús en medio de la comunidad de creyentes. El pan y el vino consagrados recuerdan el sacrificio de Cristo y se consideran su cuerpo y su sangre. De este modo, son verdadera presencia de Jesús, alimento material y espiritual para los fieles.</p>
                                <p><strong>El signo visible de la eucaristía es el pan y el vino</strong>, mezclado con un poco de <strong>agua</strong>. El pan representa el fruto del trabajo cotidiano. El vino representa la plenitud de la vida y lo divino. Una vez consagrados, pan y vino son cuerpo y sangre de Cristo. El agua representa a la humanidad, lo que implica que los fieles están presentes en la ofrenda.</p>
                                <p>La oración que consagra el pan y el vino como cuerpo y sangre de Cristo es la siguiente: «Tomen y coman todos de él, porque esto es mi Cuerpo que será entregado por ustedes». «Tomen y beban todos de él, porque ésta es mi Sangre. Sangre de la alianza nueva y eterna que será derramada por ustedes y por todos los hombres para el perdón de los pecados».</p>
                                <h3>Fundamentos bíblicos de la eucaristía</h3>
                                <p>El fundamento bíblico de la eucaristía se encuentra en las narraciones evangélicas de la Última Cena. Según los evangelistas, Jesús tomó el pan y el vino, los bendijo y los repartió como signo de su cuerpo y sangre. Hecho esto, les pidió repetir este gesto en su memoria (ver Mateo 26, 17-30; Marcos 14:12-25; Juan 13, 1-15; Lucas 22, 7-20). Otras referencias son: Juan 6, 30-35; Juan 6, 48-58; Primera Carta a los Corintios 10, 16 y 11, 23-29.</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">3. Confirmación</h5>
                                <img alt="7 sacramentos" src="../assets/images/post/Sacramentos/confirmacion.jpg">
                                <p class="card-text">Ceremonia de confirmación. El obispo unge el Santo Crisma en la frente del joven confirmando, mientras su padrino coloca su mano derecha sobre su hombro, como símbolo de apoyo y orientación en la fe. </p>
                                <p>La confirmación es un sacramento de iniciación que consiste en la renovación de las promesas bautismales. Entre ellas, el rechazo del pecado, el seguimiento del evangelio y el compromiso con la Iglesia. El ministro es el obispo, quien pueden delegar la función en un sacerdote. El sujeto puede ser cualquier persona bautizada que haya tomado la primera comunión.</p>
                                <h3>Significado de la confirmación</h3>
                                <p>La confirmación simboliza la reafirmación de la fe y del compromiso cristiano, acrecentados por los dones del Espíritu Santo: sabiduría, inteligencia, consejo, fortaleza, ciencia, piedad y temor de Dios.</p>
                                <p><strong>La materia o signo visible de la confirmación es la unción del Santo Crisma</strong>, un aceite perfumado que simboliza el fortalecimiento de la fe y el llamado a ser testimonio. Imposición de manos, la cual transmite la bendición de Dios.</p>
                                <p>La oración por la cual se sella el sacramento de la confirmación es: «Recibe por esta señal el don del Espíritu Santo». Una vez pronunciada por el obispo, la persona ya está <em>confirmada</em>.</p>
                                <p>Otros elementos complementarios de la liturgia de confirmación son: la <strong>luz del cirio pascual y el beso de la paz. </strong>La luz es símbolo del Espíritu Santo que da vida. El beso es señal de la comunión del obispo con los fieles.</p>
                                <h3>Fundamentos bíblicos de la confirmación</h3>
                                <p>El principal fundamento bíblico de la confirmación se encuentra en el pasaje de Pentecostés, de los Hechos de los Apóstoles (Hechos 2, 1-13). Según el libro, después de la muerte y resurrección de Jesús, los apóstoles se escondieron por miedo.</p>
                                <p>Al final de cincuenta días, el Espíritu Santo se derramó sobre ellos, fortaleció su fe, los colmó de dones y los envió a predicar el Evangelio. Otras referencias son: Hechos de los Apóstoles 19, 1-6. Segunda Carta a los Corintios 1, 21-22. Efesios 1, 13. Hebreos 6, 1-2.</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">4. Matrimonio</h5>
                                <img alt="7 sacramentos" src="../assets/images/post/Sacramentos/matrimonio.jpg">
                                <p class="card-text">El matrimonio es un sacramento de servicio que consiste en la unión entre el hombre y la mujer ante Dios, con el propósito de fundar una familia cristiana. Cualquier persona confirmada y soltera puede contraer matrimonio eclesiástico. Si un miembro de la pareja no es católico, se puede solicitar un permiso especial, el cual es otorgado por el obispo y recibe el nombre de <em>dispensa</em>.</p>
                                <h3>Significado del matrimonio</h3>
                                <p>El matrimonio simboliza el amor y la entrega mutua de la pareja en un proyecto de vida común, basado en la fidelidad y el servicio. La familia que se constituye por el matrimonio representa la unidad primordial de la Iglesia y la sociedad, ya que en ella se enseñan y multiplican los valores de convivencia.</p>
                                <p>Por esto, el matrimonio es el único sacramento en que los contrayentes actúan a la vez como ministros, sujetos y signo visible. Solo en este caso, el sacerdote actúa como testigo cualificado y transmite la bendición de Dios a la pareja.</p>
                                <p>La expresión que sella el sacramento del matrimonio es el consentimiento mutuo de la unión, que se produce cuando los miembros de la pareja dicen: «Sí, acepto».</p>
                                <p>Los símbolos complementarios del bautismo son los anillos y las arras. Los <strong>anillos </strong>simbolizan la alianza amorosa y la entrega mutua de los esposos. Las <strong>arras </strong>(monedas)simbolizan la comunión de los bienes materiales y espirituales de la pareja.</p>
                                <h3>Fundamentos bíblicos del matrimonio</h3>
                                <p>El fundamento bíblico del sacramento del matrimonio se encuentra en el libro del Génesis (capítulos 1 y 2), donde se habla de la formación de la primera pareja, Adán y Eva. El evangelio también sustenta el sacramento del matrimonio. Allí, Jesús refrenda el vínculo sagrado entre hombre y mujer, y se pronuncia en contra del acta de repudio (Mateo 5, 31-32; Mateo 19, 1-12; Marcos 10, 1-12; Lucas 16, 18).</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">5. Orden sacerdotal</h5>
                                <img alt="7 sacramentos" src="../assets/images/post/Sacramentos/Orden.jpg">
                                <p class="card-text">Ordenación sacerdotal. En el centro y sobre la alfombra, se encuentran los aspirantes a sacerdotes en posición de postración, en señal de entrega absoluta y humildad. Fotografía original de la Oficina de Información del Opus Dei.</p>
                                <p>El orden sacerdotal es un sacramento de servicio por medio del cual un aspirante (varón bautizado) se convierte en presbítero (sacerdote) al servicio de la Iglesia. Sus funciones principales son evangelizar y administrar los sacramentos. El ministro que impone el orden sacerdotal es el obispo. El sujeto que puede recibirla es el varón varón soltero, bautizado y confirmado.</p>
                                <h3>Significado del orden sacerdotal</h3>
                                <p>El orden sacerdotal simboliza la consagración absoluta de la persona al seguimiento de Jesús. Tanto el sacerdocio como otras formas de ordenación religiosa expresan el compromiso exclusivo y permanente con la fe cristiana, el servicio a la Iglesia y la evangelización.</p>
                                <p><strong>La materia o signo visible del sacramento es la imposición de manos </strong>del obispo sobre el aspirante. La oración que se pronuncia para sellar el sacramento recibe el nombre de oración consecratoria. Reza de la siguiente forma:</p>
                                <p>«Te pedimos, Padre Todopoderoso, que confieras a estos siervos tuyos la dignidad del presbiterado; renueva en sus corazones el Espíritu de santidad; reciban de Ti el sacerdocio de segundo grado y sean, con su conducta, ejemplo de vida».</p>
                                <h3>Fundamentos bíblicos de la ordenación sacerdotal</h3>
                                <p>La ordenación sacerdotal tiene sus fundamentos en el evangelio, particularmente en los relatos de la Última Cena. De acuerdo con este pasaje, Jesús le pidió a sus apóstoles repetir el memorial de la Santa Cena en su nombre y servir a sus semejantes, para lo cual dio el ejemplo al lavarles los pies (ver Mateo 26, 17-35; Marcos 14, 12-22; Juan 13; Jn 15; Lucas 22).</p>
                                <p>Otras referencias son: Lucas 10, 16. Hechos de los Apóstoles 6, 6. Hechos de los Apóstoles 15, 2-6. Hechos de los Apóstoles 20, 17. Hechos de los Apóstoles 21, 18. Primera Carta de Timoteo 4, 14. Carta a Tito 1, 5.</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">6. Reconciliación</h5>
                                <img alt="7 sacramentos" src="../assets/images/post/Sacramentos/Penitencia.jpg">
                                <p class="card-text">Sacramento de la reconciliación, celebrado en un espacio al aire libre. La estola morada representa la función que cumple el sacerdote para otorgar el perdón en nombre de Dios. </p>
                                <p>La reconciliación es un sacramento de curación que consiste en confesar los pecados y recibir el perdón de Dios a través del sacerdote. También recibe el nombre de confesión o penitencia, aunque este último ya no se usa. El ministro es el sacerdote común. En caso extraordinario y según la gravedad, puede ser el obispo o el Papa. El sacramento está destinado a todo bautizado que se sienta en falta (pecado).</p>
                                <h3>Significado de la reconciliación</h3>
                                <p>La reconciliación representa la misericordia infinita de Dios frente a la fragilidad humana y la oportunidad de caminar hacia la santidad. Es asimismo fuente de paz de conciencia y auxilio espiritual ante la tentación.</p>
                                <p><strong>El signo o materia visible de la reconciliación es la confesión </strong>de los pecados ante el sacerdote. La persona debe preparar un buen examen de conciencia, hacer un acto de contrición (arrepentimiento); tener propósito de enmienda (reparar el daño) y cumplir la penitencia.</p>
                                <p>Para dar la absolución de los pecados y sellar el acto de reconciliación, se puede usar una oración corta o una oración larga. Estas oraciones son:</p>
                                <ul>
                                    <li>
                                        <strong>Forma corta:</strong> «Yo te absuelvo de tus pecados en el nombre del Padre, del Hijo y del Espíritu Santo».
                                    </li>
                                    <li>
                                        <strong>Forma larga:</strong> «Dios, Padre misericordioso, que reconcilió consigo al mundo por la muerte y la resurrección de su Hijo y derramó el Espíritu Santo para la remisión de los pecados, te conceda, por el ministerio de la Iglesia, el perdón y la paz. Y yo te absuelvo de tus pecados en el nombre del Padre y del Hijo y del Espíritu Santo».
                                    </li>
                                </ul>
                                <h3>Fundamentos bíblicos de la reconciliación</h3>
                                <p>Para los católicos, el Nuevo Testamento autoriza a los sacerdotes a transmitir el perdón de Dios a los penitentes. Por ejemplo: «Recibid el Espíritu Santo; a quienes les perdonéis los pecados, les son perdonados; a quienes se los retengáis, les son retenidos» (Juan 20, 22-23).</p>
                                <p>También: «A ti (Pedro) te daré las llaves del Reino de los Cielos; y lo que ates en la Tierra quedará atado en los Cielos, y lo que desates en la tierra quedará desatado en los Cielos» (Mateo 16, 19).</p>
                                <p>Otras referencias se pueden encontrar en: Mateo 18, 18. Lucas 15, 18-19. Juan 20, 21-23. Hechos de los Apóstoles 19, 18. Primera Carta a los Corintios 5, 3-5. Segunda Carta a los Corintios 2, 6-11. Carta a los Corintios 5, 18-20. Carta de Santiago 5, 16. Primera Carta de Juan 1, 8-9.</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">7. Unción de los enfermos</h5>
                                <img alt="7 sacramentos" src="../assets/images/post/Sacramentos/Extremauncion.jpg">
                                <p class="card-text">Escena de unción de los enfermos (antiguamente extrema unción). El sacerdote es acompañado por un joven acólito que lo asiste en su servicio.</p>
                                <p>La unción de los enfermos es un sacramento de curación que consiste en transmitir la gracia de Dios, el consuelo y la fortaleza a las personas enfermas de gravedad (no necesariamente en peligro de muerte). El ministro es el sacerdote. El sacramento está dirigido a cualquier persona bautizada que se encuentre enferma, incapacitada o en peligro de muerte.</p>
                                <h3>Significado de la unción de los enfermos</h3>
                                <p>El sacramento de la unción de los enfermos transmite la presencia de Cristo. Brinda fortaleza y consuelo en la enfermedad, da paz de conciencia en el lecho de muerte y otorga el perdón de las culpas para la vida eterna.</p>
                                <p><strong>El signo visible del sacramento (materia) es la unión de los Santos Óleos</strong>, que consiste en aceite de oliva bendecido que se aplica sobre el sujeto haciendo la señal la cruz.</p>
                                <p>La oración que sella el sacramento de la unción de los enfermos es: «Por esta santa Unción (se unge el aceite con la señal de la cruz) y su benignísima misericordia, te perdone el Señor todo lo que has pecado por medio de la vista, el oído, el olfato, el gusto y la palabra, el tacto, el andar. Así sea».</p>
                                <h3>Fundamentos bíblicos de la unción de los enfermos</h3>
                                <p>Las bases de este sacramento se registran en los pasajes bíblicos según los cuales Jesús atendió a los enfermos. Para la Iglesia católica, Jesús hizo sentir la presencia de Dios al confortar y sanar a los enfermos (ver Marcos 6,13; Lucas 13, 12-13).</p>
                                <p>Por otra parte, la Carta de Santiago instruye sobre asistir a los enfermos para llevar sanidad física y/o espiritual, signos de la gracia divina (Santiago 5,14-15). Otras referencias son: Hechos de los Apóstoles 9, 17-18; Primera Carta a los Corintios 12, 9.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ingrese sus datos</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <?php
                if (isset($_SESSION['usuario']) || isset($_SESSION['CiPersona'])) {
                    echo '<h2 class="mb-48 text-center">Oops..</h2>';
                    echo '<h6 class="mb-48 text-center">Ya tienes una sesion activa</h6><br>';
                    echo '<a href="logout.php" class="btn btn-lg btn-danger-soft"> Cerrar Session</a>';
                } else {
                ?>
                    <div class="modal-body">
                        <form action="../Controlador/controladorLogin.php" method="POST" class="form-validator"
                            id="login-form">
                            <div class="mb-3 position-relative input-group-lg">
                                <input type="text" class="form-control" placeholder="Usuario" id="username"
                                    name="username">
                            </div>
                            <div class="mb-3">
                                <div class="input-group input-group-lg">
                                    <input class="form-control fakepassword" type="password" id="password"
                                        name="password" placeholder="Contraseña">
                                    <span class="input-group-text p-0">
                                        <i class="fakepasswordicon fa-solid fa-eye-slash cursor-pointer p-2 w-40px"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-lg btn-primary-soft">Ingresar</button>
                            </div>
                        </form>
                    </div>
                <?php }
                ?>
            </div>
        </div>
    </div>




    <footer class="pt-1 bg-mode">
        <hr class="mb-0 mt-1">
        <div class="bg- light py-3">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <p class="text-center mb-0">©2026 <a class="text-body" target="_blank"
                                href="https://www.facebook.com/ParroquiaSanPedrodeSacaba/">Parroquia San Pedro </a>de
                            Sacaba.</p>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var loginForm = document.getElementById("login-form");
            var forgotPasswordForm = document.getElementById("forgot-password-form");
            var forgotPasswordLink = document.getElementById("forgot-password-link");
            var backToLoginLink = document.getElementById("back-to-login-link");
            var forgotPasswordBlock = document.getElementById("forgot-password-block");

            forgotPasswordLink.addEventListener("click", function(e) {
                e.preventDefault();
                loginForm.style.display = "none";
                Menu.style.display = "None"
                Menu1.style.display = "None"
                forgotPasswordBlock.style.display = "block";
            });

            backToLoginLink.addEventListener("click", function(e) {
                e.preventDefault();
                loginForm.style.display = "block";
                Menu.style.display = "block"
                Menu1.style.display = "block"
                forgotPasswordBlock.style.display = "none";
            });
        });


        const showVideoButton = document.getElementById('showVideoButton');
        const videoContainer = document.getElementById('videoContainer');
        const closeVideoButton = document.getElementById('closeVideoButton');
        const videoPlayer = document.getElementById('videoPlayer');

        showVideoButton.addEventListener('click', function() {
            videoContainer.style.display = 'block';
            showVideoButton.style.display = 'none';
            videoPlayer.play();
        });

        closeVideoButton.addEventListener('click', function() {
            videoContainer.style.display = 'none';
            showVideoButton.style.display = 'block';
            videoPlayer.pause();
        });


        document.addEventListener('DOMContentLoaded', function() {
            document.documentElement.setAttribute('data-bs-theme', 'light');

            localStorage.setItem('theme', 'light');

            const modeSwitchButtons = document.querySelectorAll('.btn-modeswitch');
            modeSwitchButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const theme = this.getAttribute('data-bs-theme-value');
                    document.documentElement.setAttribute('data-bs-theme', theme);
                    localStorage.setItem('theme', theme);
                });
            });
        });
    </script>
    <script src="../assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/vendor/plyr/plyr.js"></script>
    <script src="../assets/vendor/tiny-slider/dist/tiny-slider.js"></script>
    <script src="../assets/js/functions.js"></script>
    <script src="../assets/js/tema.js"></script>
</body>

</html>