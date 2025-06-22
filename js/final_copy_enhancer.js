// 表格复制增强功能
const templates = {
  wxwork: {
    lineBreak: '\n',
    divider: '─────────────────────'
  },
  default: {
    lineBreak: '\n',
    divider: '──────────────'
  }
};

let approvedTemplate = null;

function approveTemplate(templateName) {
  approvedTemplate = templates[templateName] || templates.default;
}

let welcomeMessages = [];
let closingMessages = [];

async function loadTemplates() {
  try {
    // 加载欢迎语模板
    const welcomeResponse = await fetch('templates/welcome_messages.json');
    welcomeMessages = await welcomeResponse.json();
    
    // 加载结束语模板
    const closingResponse = await fetch('templates/closing_messages.json');
    closingMessages = await closingResponse.json();
  } catch (error) {
    console.error('加载模板失败:', error);
    // 使用默认值
    welcomeMessages = [
      '尊敬的客户您好，这是您的保单信息：',
      '您好，保单信息如下：',
      '这是您需要的保单详情：'
    ];
    closingMessages = [
      '以上信息请查收，如有疑问请联系客服。',
      '感谢您的信任，祝您生活愉快！',
      '保单信息仅供参考，具体以合同为准。'
    ];
  }
}

function getRandomWelcome() {
  if (welcomeMessages.length === 0) {
    console.warn('欢迎语模板未加载，使用默认值');
    return '您好，这是您的保单信息：';
  }
  return welcomeMessages[Math.floor(Math.random() * welcomeMessages.length)];
}

function getRandomClosing() {
  if (closingMessages.length === 0) {
    console.warn('结束语模板未加载，使用默认值');
    return '感谢您的信任，祝您生活愉快！';
  }
  return closingMessages[Math.floor(Math.random() * closingMessages.length)];
}

function getTableText(tableElement) {
  // 获取所有行
  const rows = Array.from(tableElement.querySelectorAll('tr'));
  if (rows.length === 0) {
    console.error('表格无数据');
    return '';
  }

  // 获取每列最大宽度
  const colWidths = [];
  rows.forEach(row => {
    const cells = Array.from(row.querySelectorAll('th, td'));
    cells.forEach((cell, i) => {
      const text = cell.innerText.trim();
      colWidths[i] = Math.max(colWidths[i] || 0, text.length);
    });
  });

  // 格式化每行
  const formattedRows = rows.map(row => {
    const cells = Array.from(row.querySelectorAll('th, td'));
    return cells.map((cell, i) => {
      const text = cell.innerText.trim();
      return text.padEnd(colWidths[i], ' ');
    }).join('  '); // 两空格分隔列
  });

  // 生成分隔线
  const divider = '-'.repeat(formattedRows[0].length);

  // 组合结果: 首行 + 分隔线 + 内容行
  return [
    formattedRows[0], // 表头
    divider,
    ...formattedRows.slice(1) // 数据行
  ].join('\n');
}

function showFeedback(message, element) {
  const originalText = element.textContent;
  element.textContent = message;
  setTimeout(() => {
    element.textContent = originalText;
  }, 2000);
}

async function enhanceTableCopy() {
  // 确保模板加载
  if (!approvedTemplate) approveTemplate('wxwork');
  
  // 加载消息模板
  await loadTemplates();
  console.log('消息模板加载完成');

  // 添加按钮样式
  const style = document.createElement('style');
  style.textContent = `
    .copied-btn {
      background-color: #07C160 !important;
      color: white !important;
    }
  `;
  document.head.appendChild(style);

  document.addEventListener('click', async function(e) {
    // 支持多种按钮选择方式
    const copyBtn = e.target.closest('[data-copy-table], .copy-table-btn');
    if (!copyBtn) return;
    
    e.preventDefault();
    
    // 查找表格
    const table = copyBtn.closest('.modal-content')?.querySelector('table') || 
                 document.querySelector('.renewal-table, table');
    if (!table) {
      showFeedback('未找到表格', copyBtn);
      return;
    }

    // 获取完整内容
    const welcome = getRandomWelcome();
    const closing = getRandomClosing();
    let tableText = getTableText(table);

    // 构建最终文本
    const finalText = [
      welcome,
      '',
      tableText,
      '',
      closing
    ].join('\n');
    
    // 执行复制
    try {
      await navigator.clipboard.writeText(finalText);
      showFeedback('复制成功', copyBtn);
      console.log('已复制内容:', finalText);
    } catch (err) {
      console.error('复制失败:', err);
      showFeedback('复制失败', copyBtn);
    }
  });
}

// 初始化
enhanceTableCopy();