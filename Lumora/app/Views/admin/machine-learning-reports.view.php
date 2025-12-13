<div class="ml-reports-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1>🤖 Smart Search & Auto-Tagging Analytics</h1>
            <p>Monitor the performance and efficiency of your machine learning features</p>
        </div>

        <!-- Date Filters -->
        <div class="filters-section">
            <form method="GET" action="/admin/machine-learning-reports">
                <div class="date-filters">
                    <div class="form-group">
                        <label for="start_date">Start Date</label>
                        <input type="date" id="start_date" name="start_date" 
                               value="<?= htmlspecialchars($dateRange['start']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="end_date">End Date</label>
                        <input type="date" id="end_date" name="end_date" 
                               value="<?= htmlspecialchars($dateRange['end']) ?>" required>
                    </div>
                    <button type="submit" class="btn-filter">Apply Filters</button>
                </div>
            </form>
        </div>

        <!-- Key Metrics Stats -->
        <div class="stats-grid">
            <div class="stat-card warning">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Products Missing Tags</div>
                        <div class="stat-value"><?= number_format($missingTagsCount) ?></div>
                        <div class="stat-description">Need manual review</div>
                    </div>
                    <div class="stat-icon">⚠️</div>
                </div>
            </div>

            <div class="stat-card success">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Auto-Generated Tags</div>
                        <div class="stat-value"><?= number_format($autoTagStats['auto_tags'] ?? 0) ?></div>
                        <div class="stat-description">ML-powered tagging</div>
                    </div>
                    <div class="stat-icon">🏷️</div>
                </div>
            </div>

            <div class="stat-card info">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Manual Tags</div>
                        <div class="stat-value"><?= number_format($autoTagStats['manual_tags'] ?? 0) ?></div>
                        <div class="stat-description">User-created tags</div>
                    </div>
                    <div class="stat-icon">✏️</div>
                </div>
            </div>

            <div class="stat-card purple">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Total Search Queries</div>
                        <div class="stat-value"><?= number_format($totalSearches ?? 0) ?></div>
                        <div class="stat-description">In selected period</div>
                    </div>
                    <div class="stat-icon">🔍</div>
                </div>
            </div>
        </div>

        <!-- Charts Grid -->
        <div class="charts-grid">
            <!-- Search Volume Trend -->
            <div class="chart-card full-width">
                <div class="chart-header">
                    <h2 class="chart-title">📈 Search Volume Trend</h2>
                    <p class="chart-subtitle">Daily search activity showing usage growth and peak demand periods</p>
                </div>
                <div class="chart-container">
                    <canvas id="searchVolumeChart"></canvas>
                </div>
            </div>

            <!-- Tag Density Distribution -->
            <div class="chart-card">
                <div class="chart-header">
                    <h2 class="chart-title">📊 Tag Density Distribution</h2>
                    <p class="chart-subtitle">Distribution of tags per product (histogram)</p>
                </div>
                <div class="chart-container small">
                    <canvas id="tagDensityChart"></canvas>
                </div>
            </div>

            <!-- Top Tags Usage -->
            <div class="chart-card">
                <div class="chart-header">
                    <h2 class="chart-title">🏆 Top 20 Tags by Usage</h2>
                    <p class="chart-subtitle">Most frequently used tags across all products</p>
                </div>
                <div class="chart-container small">
                    <canvas id="topTagsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Products Missing Tags Table -->
        <div class="chart-card full-width">
            <div class="chart-header">
                <h2 class="chart-title">🔍 Products Missing Tags (Top 20)</h2>
                <p class="chart-subtitle">Products that need manual tag assignment</p>
            </div>
            
            <?php if (empty($missingTagsList)): ?>
                <div class="empty-state">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p>All published products have tags! 🎉</p>
                </div>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Product ID</th>
                            <th>Product Name</th>
                            <th>Shop</th>
                            <th>Created Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($missingTagsList as $product): ?>
                        <tr>
                            <td><?= htmlspecialchars($product['product_id']) ?></td>
                            <td><?= htmlspecialchars($product['name']) ?></td>
                            <td><?= htmlspecialchars($product['shop_name']) ?></td>
                            <td><?= date('M d, Y', strtotime($product['created_at'])) ?></td>
                            <td>
                                <a href="/products/<?= htmlspecialchars($product['slug']) ?>" 
                                   class="badge badge-info">View Product</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>