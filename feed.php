<?php 
require 'config.php';
if (!isLoggedIn()) {
    redirect('login.php');
}

// Get all posts
$stmt = $pdo->query("
    SELECT posts.*, users.username 
    FROM posts 
    JOIN users ON posts.user_id = users.id 
    ORDER BY posts.created_at DESC
");
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feed | BlogSpace</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
            background: #f5f5f5;
            color: #333;
        }
        
        header {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #6e8efb;
        }
        
        nav ul {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        
        nav ul li {
            margin-left: 20px;
        }
        
        nav ul li a {
            text-decoration: none;
            color: #555;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        nav ul li a:hover {
            color: #6e8efb;
        }
        
        .btn {
            padding: 8px 16px;
            background: #6e8efb;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #5a7df4;
        }
        
        main {
            padding: 30px 0;
        }
        
        .post {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            padding: 20px;
            margin-bottom: 30px;
            transform: translateY(20px);
            opacity: 0;
            animation: fadeInUp 0.5s forwards;
        }
        
        @keyframes fadeInUp {
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .post-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .profile-pic {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px;
            border: 2px solid #6e8efb;
        }
        
        .post-user {
            font-weight: bold;
        }
        
        .post-time {
            font-size: 12px;
            color: #999;
        }
        
        .post-title {
            font-size: 20px;
            margin-bottom: 10px;
            color: #333;
        }
        
        .post-content {
            line-height: 1.6;
            color: #555;
            margin-bottom: 15px;
        }
        
        .create-post {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .form-control:focus {
            border-color: #6e8efb;
            outline: none;
        }
        
        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }
        
        /* Delay animations for each post */
        .post:nth-child(1) { animation-delay: 0.1s; }
        .post:nth-child(2) { animation-delay: 0.2s; }
        .post:nth-child(3) { animation-delay: 0.3s; }
        .post:nth-child(4) { animation-delay: 0.4s; }
        .post:nth-child(5) { animation-delay: 0.5s; }
    </style>
</head>
<body>
    <header>
        <div class="container header-content">
            <div class="logo">BlogSpace</div>
            <nav>
                <ul>
                    <li><a href="feed.php">Feed</a></li>
                    <li><a href="myprofile.php">My Profile</a></li>
                    <li><a href="?logout">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>
    
    <main>
        <div class="container">
            <?php if (isset($_GET['logout'])): ?>
                <?php 
                session_destroy();
                redirect('login.php');
                ?>
            <?php endif; ?>
            
            <div class="create-post">
                <h2>Create a New Post</h2>
                <form method="POST" action="myprofile.php">
                    <div class="form-group">
                        <input type="text" name="title" class="form-control" placeholder="Post title" required>
                    </div>
                    <div class="form-group">
                        <textarea name="content" class="form-control" placeholder="What's on your mind?" required></textarea>
                    </div>
                    <button type="submit" name="create_post" class="btn">Post</button>
                </form>
            </div>
            
            <h2>Recent Posts</h2>
            
            <?php if (empty($posts)): ?>
                <div class="post">
                    <p>No posts yet. Be the first to post something!</p>
                </div>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <div class="post">
                        <div class="post-header">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/a/ac/Default_pfp.jpg" alt="Profile" class="profile-pic">
                            <div>
                                <div class="post-user"><?= htmlspecialchars($post['username']) ?></div>
                                <div class="post-time"><?= date('F j, Y \a\t g:i a', strtotime($post['created_at'])) ?></div>
                            </div>
                        </div>
                        <h3 class="post-title"><?= htmlspecialchars($post['title']) ?></h3>
                        <div class="post-content"><?= nl2br(htmlspecialchars($post['content'])) ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>