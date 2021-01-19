<?php
session_start();
if (!isset($_SESSION['connected_id'])){
    header('Location: login.php');
    exit();
}
$userLiker = $_SESSION['connected_id'];

$mysqli = new mysqli("localhost", "root", "", "socialnetwork");
if ($mysqli->connect_errno) {
    echo ("Échec de la connexion : " . $mysqli->connect_error);
    echo ("<p>Indice: Vérifiez les parametres de <code>new mysqli(...</code></p>");
    exit();
}

$laQuestionEnSql = "SELECT `posts`.`content`,"
. "`posts`.`created`,"
. "`users`.`alias` as author_name,  "
. "`users`.`id` as user_id,  "
. "`posts`.`id` as post_id,  "
. "count(`likes`.`id`) as like_number,  "
. "GROUP_CONCAT(distinct`tags`.`label`) AS taglist "
. "FROM `posts`"
. "JOIN `users` ON  `users`.`id`=`posts`.`user_id`"
. "LEFT JOIN `posts_tags` ON `posts`.`id` = `posts_tags`.`post_id`  "
. "LEFT JOIN `tags`       ON `posts_tags`.`tag_id`  = `tags`.`id` "
. "LEFT JOIN `likes`      ON `likes`.`post_id`  = `posts`.`id` "
. "GROUP BY `posts`.`id`"
. "ORDER BY `posts`.`created` DESC  "
. "LIMIT 10";
$lesInformations = $mysqli->query($laQuestionEnSql);
$lesInformations2 = $mysqli->query($laQuestionEnSql);
// Vérification
if (!$lesInformations) {
    echo ("Échec de la requete : " . $mysqli->error);
    echo ("<p>Indice: Vérifiez les la requete  SQL suivante dans phpmyadmin<code>$laQuestionEnSql</code></p>");
    exit();
}

$likearray = array();
$postidarray = array();

    if (isset($_POST['likeButton'])) {

        $postLiked = $_POST['postId'];

        $alreadyLiked="SELECT *
        FROM `likes`
        WHERE `user_id` ='".$userLiker ."'"
        ."AND `post_id`='".$postLiked."'";

        $resultLike = $mysqli->query($alreadyLiked);
        $isLiked=($resultLike->num_rows!=0);


        if (!$isLiked) {
            $likeRequete = "INSERT INTO `likes` "
            . "(`id`, `user_id`,`post_id`) "
            . "VALUES (NULL, "
            . "" . $userLiker . ", "
            . "'" . $postLiked . "'"
            . ");";

            $infoLike = $mysqli->query($likeRequete);
            $btnLike = "Unlike";

            // verification
            if (!$infoLike) {
                echo ("Échec de la requete like : " . $mysqli->error);
                exit();
            }
        } else {
            $unlikeRequest = "DELETE
            FROM `likes`
            WHERE `user_id` ='" . $userLiker . "'"
            . "AND `post_id`='" . $postLiked . "'";

            $resultUnlike = $mysqli->query($unlikeRequest);
            $btnLike = "Like ! ♥";

        }
        header("Refresh:0");
    }
    while ($post2 = $lesInformations2->fetch_assoc()) {
        $alreadyLiked2 = "SELECT *
        FROM `likes`
        WHERE `user_id` ='" . $userLiker . "'"
        . "AND `post_id`='" . $post2['post_id'] . "'";


        $resultLike2 = $mysqli->query($alreadyLiked2);

        if ($resultLike2->num_rows !== 0){
            $isLiked2=1;
        }
        else {
            $isLiked2=0;
        }

        array_push($likearray, $isLiked2);
        array_push($postidarray,$post2['post_id']);

    }
    $postlike_array = array_combine($postidarray, $likearray);
    include 'header.php';

    ?>
        <div id="wrapper">
            <aside>
                <img src="user.jpg" alt="Portrait de l'utilisatrice" />
                <section>
                    <h3>Présentation</h3>
                    <p>Sur cette page vous trouverez les derniers messages de
                        tous les utilisatrices du site.</p>
                    </section>
                </aside>
                <main>
                    <!-- L'article qui suit est un exemple pour la présentation et
                    @todo: doit etre retiré -->

                    <?php

                    while ($post = $lesInformations->fetch_assoc()) {
                        if ($postlike_array[$post['post_id']]==1){

                                $btnLike = "Unlike";



                                } else {
                                $btnLike = "Like ! ♥";

                                }

                        ?>
                        <article>
                            <h3>
                                <time><?= $post['created'] ?></time>
                                <!--ceci est un short-tag (pareil que les lignes du dessous)-->
                            </h3>
                            <address><a href="wall.php?user_id=<?php echo $post['user_id'] ?>"> <?php echo $post['author_name'] ?></a></address>
                            <div>
                                <p><?php echo $post['content'] ?></p>

                            </div>
                            <footer>

                                <small>♥<?php echo $post['like_number'] ?></small>
                                <small>
                                    <form method="post">
                                        <input type='submit' name='likeButton' value="<?php echo $btnLike ?>">
                                        <input type="hidden" name="postId" value="<?php echo $post['post_id'] ?>">
                                    </form>
                                </small>
                                <a href=""><?php echo $post['taglist'] ?></a>,
                            </footer>
                        </article>
                        <?php
                        // avec le <?php ci-dessus on retourne en mode php
                    } // cette accolade ferme et termine la boucle while ouverte avant.
                    ?>

                </main>
            </div>
        </body>

        </html>
