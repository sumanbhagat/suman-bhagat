// Simple admin API for Vercel (Node.js serverless function)
module.exports = async (req, res) => {
  // Enable CORS
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');

  // Handle preflight requests
  if (req.method === 'OPTIONS') {
    return res.status(200).end();
  }

  try {
    const { method, query, body } = req;
    const path = req.url.split('?')[0];

    // Basic admin authentication (in production, use proper auth)
    const authHeader = req.headers.authorization;
    if (!authHeader || authHeader !== 'Bearer admin-token') {
      return res.status(401).json({ error: 'Unauthorized' });
    }

    // Route handling
    if (path === '/api/admin/login' && method === 'POST') {
      // Simple login (in production, verify against database)
      const { username, password } = body;
      if (username === 'admin' && password === 'password') {
        return res.json({
          success: true,
          token: 'admin-token',
          user: { name: 'Admin', role: 'admin' }
        });
      }
      return res.status(401).json({ error: 'Invalid credentials' });
    }

    if (path === '/api/admin/dashboard' && method === 'GET') {
      // Mock dashboard data
      return res.json({
        stats: {
          totalVisits: 1234,
          totalMessages: 56,
          totalProjects: 12,
          totalBlogPosts: 8
        },
        recentActivity: [
          { type: 'contact', message: 'New contact form submission', time: '2 hours ago' },
          { type: 'portfolio', message: 'New project added', time: '1 day ago' }
        ]
      });
    }

    if (path === '/api/admin/settings' && method === 'GET') {
      // Mock settings
      return res.json({
        siteTitle: 'My Portfolio',
        siteDescription: 'Professional portfolio website',
        authorName: 'Suman Kumar Bhagat',
        contactEmail: 'suman@example.com'
      });
    }

    // Default response
    res.status(404).json({ error: 'Endpoint not found' });

  } catch (error) {
    console.error('API Error:', error);
    res.status(500).json({ error: 'Internal server error' });
  }
};
