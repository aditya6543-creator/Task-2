<?php 
require 'config.php';
if (!isLoggedIn()) {
    redirect('login.php');
}

// Handle post creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_post'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    
    $stmt = $pdo->prepare("INSERT INTO posts (user_id, title, content) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $title, $content]);
}

// Handle post update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_post'])) {
    $post_id = $_POST['post_id'];
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    
    // Verify post belongs to user
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
    $stmt->execute([$post_id, $_SESSION['user_id']]);
    
    if ($stmt->fetch()) {
        $stmt = $pdo->prepare("UPDATE posts SET title = ?, content = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$title, $content, $post_id]);
        // Refresh the page to show updated content
        redirect('myprofile.php');
    }
}

// Handle post deletion
if (isset($_GET['delete_post'])) {
    $post_id = $_GET['delete_post'];
    
    // Verify post belongs to user
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
    $stmt->execute([$post_id, $_SESSION['user_id']]);
    if ($stmt->fetch()) {
        $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
        $stmt->execute([$post_id]);
    }
}

// Get user's posts
$stmt = $pdo->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$user_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | BlogSpace</title>
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
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn:hover {
            background: #5a7df4;
            transform: translateY(-2px);
        }
        
        .btn-danger {
            background: #e74c3c;
        }
        
        .btn-danger:hover {
            background: #c0392b;
        }
        
        main {
            padding: 30px 0;
        }
        
        .profile-header {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            padding: 30px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
        }
        
        .profile-pic-large {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 30px;
            border: 3px solid #6e8efb;
        }
        
        .profile-info h2 {
            margin: 0 0 10px 0;
            color: #333;
        }
        
        .profile-info p {
            margin: 0;
            color: #777;
        }
        
        .post {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            padding: 20px;
            margin-bottom: 30px;
            position: relative;
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
        
        .post-actions {
            display: flex;
            gap: 10px;
        }
        
        .edit-form {
            display: none;
            margin-top: 20px;
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
        
        .no-posts {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            padding: 30px;
            text-align: center;
            color: #777;
        }
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
            
            <div class="profile-header">
                <img src="https://upload.wikimedia.org/wikipedia/commons/a/ac/Default_pfp.jpg" alt="Profile" class="profile-pic-large">
                <div class="profile-info">
                    <h2><?= htmlspecialchars($_SESSION['username']) ?></h2>
                    <p>Member since <?= date('F Y', strtotime($user_posts[0]['created_at'] ?? 'now')) ?></p>
                </div>
            </div>
            
            <div class="create-post">
                <h2>Create a New Post</h2>
                <form method="POST">
                    <div class="form-group">
                        <input type="text" name="title" class="form-control" placeholder="Post title" required>
                    </div>
                    <div class="form-group">
                        <textarea name="content" class="form-control" placeholder="What's on your mind?" required></textarea>
                    </div>
                    <button type="submit" name="create_post" class="btn">Post</button>
                </form>
            </div>
            
            <h2>My Posts</h2>
            
            <?php if (empty($user_posts)): ?>
                <div class="no-posts">
                    <p>You haven't posted anything yet. Create your first post!</p>
                </div>
            <?php else: ?>
                <?php foreach ($user_posts as $post): ?>
                    <div class="post" id="post-<?= $post['id'] ?>">
                        <div class="post-header">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/a/ac/Default_pfp.jpg" alt="Profile" class="profile-pic">
                            <div>
                                <div class="post-user">You</div>
                                <div class="post-time">
                                    <?= date('F j, Y \a\t g:i a', strtotime($post['created_at'])) ?>
                                    <?php if (!empty($post['updated_at'])): ?>
                                        <br><small>(edited <?= date('F j, Y \a\t g:i a', strtotime($post['updated_at'])) ?>)</small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <h3 class="post-title"><?= htmlspecialchars($post['title']) ?></h3>
                        <div class="post-content"><?= nl2br(htmlspecialchars($post['content'])) ?></div>
                        
                        <div class="post-actions">
                            <a href="#" class="btn edit-btn" data-post-id="<?= $post['id'] ?>">Edit</a>
                            <a href="?delete_post=<?= $post['id'] ?>" class="btn btn-danger">Delete</a>
                        </div>
                        
                        <div class="edit-form" id="edit-form-<?= $post['id'] ?>" style="display: none;">
                            <form method="POST">
                                <input type="hidden" name="update_post" value="1">
                                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                <div class="form-group">
                                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($post['title']) ?>" required>
                                </div>
                                <div class="form-group">
                                    <textarea name="content" class="form-control" required><?= htmlspecialchars($post['content']) ?></textarea>
                                </div>
                                <button type="submit" class="btn">Update</button>
                                <button type="button" class="btn btn-danger cancel-edit" data-post-id="<?= $post['id'] ?>">Cancel</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Edit button functionality
            document.querySelectorAll('.edit-btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const postId = this.getAttribute('data-post-id');
                    const editForm = document.getElementById('edit-form-' + postId);
                    
                    // Hide all other edit forms first
                    document.querySelectorAll('.edit-form').forEach(form => {
                        if (form.id !== 'edit-form-' + postId) {
                            form.style.display = 'none';
                        }
                    });
                    
                    // Toggle current edit form
                    editForm.style.display = editForm.style.display === 'block' ? 'none' : 'block';
                    
                    // Scroll to the form if opening it
                    if (editForm.style.display === 'block') {
                        editForm.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }
                });
            });
            
            // Cancel button functionality
            document.querySelectorAll('.cancel-edit').forEach(button => {
                button.addEventListener('click', function() {
                    const postId = this.getAttribute('data-post-id');
                    document.getElementById('edit-form-' + postId).style.display = 'none';
                });
            });
        });
    </script>
</body>
</html>