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

// 示例欢迎语和结束语
const WELCOME_MESSAGES = [
  "您好，这是您需要的信息：",
  "请查收以下内容：",
  "这是您请求的数据："
];

const CLOSING_MESSAGES = [
  "感谢您的使用！",
  "如有疑问请随时联系。",
  "祝您工作愉快！"
];

function getRandomMessage(messages) {
  return messages[Math.floor(Math.random() * messages.length)];
}

function copyToClipboard(text) {
  return new Promise((resolve, reject) => {
    // 方法1: 优先尝试Clipboard API
    if (navigator.clipboard) {
      navigator.clipboard.writeText(text).then(resolve).catch(() => {
        // 如果Clipboard API失败，回退到方法2
        fallbackCopy(text) ? resolve() : reject();
      });
    } else {
      // 直接使用回退方法
      fallbackCopy(text) ? resolve() : reject();
    }
  });
}

function fallbackCopy(text) {
  try {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.opacity = 0;
    document.body.appendChild(textarea);
    textarea.select();
    
    const success = document.execCommand('copy');
    document.body.removeChild(textarea);
    return success;
  } catch (err) {
    console.error('回退复制失败:', err);
    return false;
  }
}

function enhanceTableCopy() {
  // 初始化按钮样式
  const style = document.createElement('style');
  style.textContent = `
    .copy-btn.copied {
      background-color: #07C160 !important;
      color: white !important;
      transition: all 0.3s;
    }
    .copy-btn.error {
      background-color: #ff4d4f !important;
      color: white !important;
      transition: all 0.3s;
    }
  `;
  document.head.appendChild(style);

  document.addEventListener('click', async function(e) {
    const btn = e.target.closest('.copy-btn');
    if (!btn) return;
    
    const originalText = btn.textContent;
    const originalClass = btn.className;
    
    try {
      const table = btn.closest('table');
      if (!table) {
        throw new Error('未找到表格');
      }

      // 构建完整内容
      const content = [
        getRandomMessage(WELCOME_MESSAGES),
        "",
        table.innerText.trim(),
        "",
        getRandomMessage(CLOSING_MESSAGES)
      ].join('\n');

      // 执行复制
      await copyToClipboard(content);
      
      // 更新按钮状态 - 成功
      btn.textContent = '✓ 已复制';
      btn.className = originalClass + ' copied';
      
    } catch (err) {
      console.error('复制失败:', err);
      btn.textContent = '复制失败';
      btn.className = originalClass + ' error';
    } finally {
      setTimeout(() => {
        btn.textContent = originalText;
        btn.className = originalClass;
      }, 2000);
    }
  });
}

// 初始化
enhanceTableCopy();