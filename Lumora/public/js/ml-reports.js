/**
 * Machine Learning Reports - Chart.js Visualizations
 * Lumora E-Commerce Platform
 * * This file contains all the Chart.js configurations for:
 * 1. Search Volume Trend (Line Chart - combines existing and new logic)
 * 2. Tag Density Distribution (Bar Chart/Histogram)
 * 3. Confidence Score Distribution (Doughnut Chart)
 * 4. Top Auto-Generated Tags (Horizontal Bar Chart - updated to Top Tags by Usage)
 * 5. Tagging Completion Progress (Line Chart - new chart)
 */

// ==================== CHART.JS GLOBAL CONFIGURATION ====================

Chart.defaults.font.family = "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif";
Chart.defaults.color = '#6b7280';

// Lumora Gold Color Scheme
const colorScheme = {
    primary: '#C9A05C',
    primaryLight: '#E8C88D',
    primaryDark: '#b38f4d',
    success: '#10b981',
    successLight: '#34d399',
    warning: '#f59e0b',
    warningLight: '#fbbf24',
    danger: '#ef4444',
    dangerLight: '#f87171',
    gray: '#6b7280',
    grayLight: '#9ca3af',
    background: 'rgba(201, 160, 92, 0.1)',
    backgroundSuccess: 'rgba(16, 185, 129, 0.1)',
    mlBlue: '#667eea', // Added for search trend/primary actions
    mlPurple: '#8b5cf6', // Added for auto-generated tags
};

// ==================== UTILITY FUNCTIONS ====================

/**
 * Format date for chart labels
 */
function formatChartDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}

/**
 * Calculate percentage
 */
function calculatePercentage(value, total) {
    return ((value / total) * 100).toFixed(1);
}

// ==================== 1. SEARCH VOLUME TREND CHART (Combined) ====================

function initSearchTrendChart(searchTrendData) {
    const ctx = document.getElementById('searchVolumeChart') || document.getElementById('searchTrendChart'); // Use new ID
    if (!ctx) {
        console.error('Search Trend Chart canvas not found');
        return;
    }
    
    new Chart(ctx.getContext('2d'), {
        type: 'line',
        data: {
            labels: searchTrendData.map(d => formatChartDate(d.date)),
            datasets: [
                // Original: Total Searches (Gold)
                {
                    label: 'Total Searches',
                    data: searchTrendData.map(d => parseInt(d.search_count)),
                    borderColor: colorScheme.primary,
                    backgroundColor: colorScheme.background,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: colorScheme.primary,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    borderWidth: 2
                },
                // Original: Unique Users (Success/Green)
                {
                    label: 'Unique Users',
                    data: searchTrendData.map(d => parseInt(d.unique_users)),
                    borderColor: colorScheme.success,
                    backgroundColor: colorScheme.backgroundSuccess,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: colorScheme.success,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    borderWidth: 2
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    align: 'end',
                    labels: {
                        usePointStyle: true,
                        padding: 15,
                        font: {
                            size: 12,
                            weight: '500'
                        }
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    cornerRadius: 8,
                    titleFont: {
                        size: 13,
                        weight: '600'
                    },
                    bodyFont: {
                        size: 12
                    },
                    callbacks: {
                        // Keep original afterBody for unique queries if data is available
                        afterBody: function(context) {
                            const index = context[0].dataIndex;
                            const uniqueQueries = searchTrendData[index].unique_queries;
                            return uniqueQueries ? '\nUnique Queries: ' + uniqueQueries : '';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                        font: {
                            size: 11
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                        drawBorder: false
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 11
                        }
                    }
                }
            }
        }
    });
}

// ==================== 2. TAG DENSITY DISTRIBUTION CHART ====================

function initTagDensityChart(tagDensityData) {
    const ctx = document.getElementById('tagDensityChart');
    if (!ctx) {
        console.error('Tag Density Chart canvas not found');
        return;
    }
    
    new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: tagDensityData.map(d => {
                const count = parseInt(d.tag_count);
                // Handle the 'No tags' case based on original JS logic
                return count === 0 ? 'No tags' : count + ' tag' + (count !== 1 ? 's' : '');
            }),
            datasets: [{
                label: 'Number of Products',
                data: tagDensityData.map(d => parseInt(d.product_count)),
                // Use color logic from original JS
                backgroundColor: tagDensityData.map(d => {
                    const count = parseInt(d.tag_count);
                    if (count === 0) return colorScheme.danger;
                    if (count < 3) return colorScheme.warning;
                    return colorScheme.primary;
                }),
                borderRadius: 6,
                borderSkipped: false,
                hoverBackgroundColor: tagDensityData.map(d => {
                    const count = parseInt(d.tag_count);
                    if (count === 0) return colorScheme.dangerLight;
                    if (count < 3) return colorScheme.warningLight;
                    return colorScheme.primaryLight;
                })
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        title: function(context) {
                            return context[0].label;
                        },
                        label: function(context) {
                            const value = context.parsed.y;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = calculatePercentage(value, total);
                            return 'Products: ' + value + ' (' + percentage + '%)';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                        font: {
                            size: 11
                        }
                    },
                    title: {
                        display: true,
                        text: 'Number of Products',
                        font: {
                            weight: '600',
                            size: 12
                        },
                        padding: { top: 10, bottom: 10 }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                        drawBorder: false
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Tags per Product',
                        font: {
                            weight: '600',
                            size: 12
                        },
                        padding: { top: 10 }
                    },
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 10
                        }
                    }
                }
            }
        }
    });
}

// ==================== 3. CONFIDENCE SCORE DISTRIBUTION CHART ====================

function initConfidenceChart(confidenceData) {
    const ctx = document.getElementById('confidenceChart');
    if (!ctx) {
        console.error('Confidence Chart canvas not found');
        return;
    }
    
    new Chart(ctx.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: confidenceData.map(d => d.confidence_range),
            datasets: [{
                data: confidenceData.map(d => parseInt(d.tag_count)),
                // Use color logic from original JS
                backgroundColor: [
                    colorScheme.success,      // Very High - Green
                    colorScheme.primary,      // High - Gold
                    colorScheme.warning,      // Medium - Orange
                    colorScheme.danger        // Low - Red
                ],
                borderWidth: 2,
                borderColor: '#ffffff',
                hoverBorderWidth: 3,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        usePointStyle: true,
                        font: {
                            size: 12,
                            weight: '500'
                        },
                        // Custom label generation to include percentage
                        generateLabels: function(chart) {
                            const data = chart.data;
                            return data.labels.map((label, i) => {
                                const value = data.datasets[0].data[i];
                                const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                                const percentage = calculatePercentage(value, total);
                                return {
                                    text: label + ' (' + percentage + '%)',
                                    fillStyle: data.datasets[0].backgroundColor[i],
                                    hidden: false,
                                    index: i
                                };
                            });
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = calculatePercentage(context.parsed, total);
                            return context.label + ': ' + context.parsed + ' tags (' + percentage + '%)';
                        },
                        // Keep original afterLabel for Avg Score
                        afterLabel: function(context) {
                            const index = context.dataIndex;
                            const avgScore = confidenceData[index].avg_score;
                            return 'Avg Score: ' + parseFloat(avgScore).toFixed(3);
                        }
                    }
                }
            }
        }
    });
}

// ==================== 4. TOP TAGS BY USAGE CHART (Combined) ====================

function initTopAutoTagsChart(topAutoTagsData) {
    // The canvas ID from the HTML is 'topTagsChart', but the function name is initTopAutoTagsChart.
    // We will update the function to handle the new data structure (auto_generated_count, manual_count)
    // and use the more general ID/title.
    const ctx = document.getElementById('topTagsChart');
    if (!ctx) {
        console.error('Top Tags Chart canvas not found');
        return;
    }
    
    new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: topAutoTagsData.map(d => d.name), // Use 'name' from new data structure
            datasets: [
                // Auto-Generated dataset (Purple from new script)
                {
                    label: 'Auto-Generated',
                    data: topAutoTagsData.map(d => parseInt(d.auto_generated_count)),
                    backgroundColor: colorScheme.mlPurple,
                    borderColor: colorScheme.mlPurple,
                    borderWidth: 1,
                    borderRadius: 6,
                    borderSkipped: false,
                    stack: 'Stack 0',
                },
                // Manual dataset (Blue from new script)
                {
                    label: 'Manual',
                    data: topAutoTagsData.map(d => parseInt(d.manual_count)),
                    backgroundColor: colorScheme.mlBlue,
                    borderColor: colorScheme.mlBlue,
                    borderWidth: 1,
                    borderRadius: 6,
                    borderSkipped: false,
                    stack: 'Stack 0',
                }
            ]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        title: function(context) {
                            return '🏷️ ' + context[0].label;
                        },
                        label: function(context) {
                            const datasetLabel = context.dataset.label || '';
                            return datasetLabel + ': ' + context.parsed.x + ' products';
                        },
                        // After label is complex due to stacking; keeping basic for now.
                        // Can't easily display combined avg confidence score here without custom logic.
                    }
                }
            },
            scales: {
                x: {
                    stacked: true,
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                        font: {
                            size: 11
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                        drawBorder: false
                    }
                },
                y: {
                    stacked: true,
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 11,
                            weight: '500'
                        }
                    }
                }
            }
        }
    });
}

// ==================== 5. TAGGING COMPLETION PROGRESS CHART (New) ====================

function initTaggingProgressChart(taggingProgressData) {
    const ctx = document.getElementById('taggingProgressChart');
    if (!ctx) {
        console.error('Tagging Progress Chart canvas not found');
        return;
    }

    new Chart(ctx.getContext('2d'), {
        type: 'line',
        data: {
            labels: taggingProgressData.map(d => formatChartDate(d.date)),
            datasets: [
                {
                    label: 'Products Created',
                    data: taggingProgressData.map(d => parseInt(d.products_created)),
                    borderColor: colorScheme.gray,
                    backgroundColor: colorScheme.grayLight,
                    tension: 0.4,
                    fill: false,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    borderWidth: 2
                },
                {
                    label: 'Products Tagged',
                    data: taggingProgressData.map(d => parseInt(d.products_tagged)),
                    borderColor: colorScheme.success,
                    backgroundColor: colorScheme.backgroundSuccess,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    borderWidth: 2
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    cornerRadius: 8,
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

// ==================== INITIALIZATION ====================

/**
 * Global initialization function that uses data passed from the PHP script tag.
 * Renamed to initializeAllCharts to avoid conflict with the existing structure.
 */
function initializeAllCharts(data) {
    // 1. Search Volume Trend
    if (data.searchVolume) {
        initSearchTrendChart(data.searchVolume);
    }
    
    // 2. Tag Density Distribution
    if (data.tagDensity) {
        initTagDensityChart(data.tagDensity);
    }
    
    // 3. Confidence Score Distribution
    if (data.confidenceDistribution) {
        initConfidenceChart(data.confidenceDistribution);
    }
    
    // 4. Top Tags by Usage (Auto-Generated/Manual split)
    if (data.topTags) {
        initTopAutoTagsChart(data.topTags);
    }

    // 5. Tagging Completion Progress
    if (data.taggingProgress) {
        initTaggingProgressChart(data.taggingProgress);
    }
}

// Note: The global initializeMLCharts from the original file is no longer needed
// since the HTML script block calls initializeAllCharts directly (or should be updated to).

// Export for module usage (if needed)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        initSearchTrendChart,
        initTagDensityChart,
        initConfidenceChart,
        initTopAutoTagsChart,
        initTaggingProgressChart,
        initializeAllCharts
    };
}

// ==================== AUTO-INITIALIZATION CALL (Simulated from the HTML script) ====================
// This section simulates the data pass-through from the HTML, assuming you will load this JS file
// and then define the PHP variables as JS objects in a subsequent <script> tag.
// We will only define a placeholder function to be called from the final HTML template.

/*
// Example of how the data will be initialized in the HTML file:

<script>
    const searchVolumeData = <?= json_encode($searchVolume) ?>;
    const tagDensityData = <?= json_encode($tagDensity) ?>;
    const confidenceData = <?= json_encode($confidenceDistribution) ?>;
    const topTagsData = <?= json_encode($topTags) ?>;
    const taggingProgressData = <?= json_encode($taggingProgress) ?>;

    initializeAllCharts({
        searchVolume: searchVolumeData,
        tagDensity: tagDensityData,
        confidenceDistribution: confidenceData,
        topTags: topTagsData,
        taggingProgress: taggingProgressData
    });
</script>
*/