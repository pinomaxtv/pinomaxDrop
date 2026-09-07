import React, { useState, useEffect } from 'react';
import { Search, ShieldAlert, Clock, Download, ShieldCheck, FileText, Upload, Wallet, Code, User, Box, PlaySquare, FileCog } from 'lucide-react';

export default function App() {
  const [view, setView] = useState<'feed' | 'verify' | 'dashboard'>('feed');

  return (
    <div className="h-screen w-full bg-[#050608] text-[#e0e0e0] font-sans flex overflow-hidden selection:bg-[#00e5ff] selection:text-black">
      
      {/* SIDEBAR */}
      <aside className="w-64 bg-[#0a0c10] border-r border-[#00e5ff33] flex flex-col p-6 shadow-[4px_0_24px_rgba(0,229,255,0.05)] z-20 shrink-0">
        <div className="mb-10 flex items-center gap-3">
          <div className="w-10 h-10 bg-[#00e5ff] rounded-lg flex items-center justify-center shadow-[0_0_15px_rgba(0,229,255,0.4)]">
            <Download size={24} className="text-black" />
          </div>
          <span className="text-xl font-bold tracking-tighter text-white uppercase">Pino<span className="text-[#00e5ff]">Drop</span></span>
        </div>
        
        <nav className="flex-1 space-y-2">
          <div className="text-[10px] uppercase tracking-widest text-[#00e5ff99] mb-4 font-semibold">Main Hub</div>
          <button onClick={() => setView('feed')} className={`w-full flex items-center gap-3 px-4 py-3 rounded-r-md transition-colors ${view === 'feed' ? 'bg-[#00e5ff11] border-l-2 border-[#00e5ff] text-white' : 'text-gray-500 hover:text-white'}`}>
            <Box size={18} />
            <span className="text-sm font-medium">Asset Market</span>
          </button>
          <button onClick={() => setView('dashboard')} className={`w-full flex items-center gap-3 px-4 py-3 rounded-r-md transition-colors ${view === 'dashboard' ? 'bg-[#00e5ff11] border-l-2 border-[#00e5ff] text-white' : 'text-gray-500 hover:text-white'}`}>
            <User size={18} />
            <span className="text-sm font-medium">Creator Dashboard</span>
          </button>
        </nav>
        
        <div className="mt-auto p-4 bg-[#00e5ff08] rounded-xl border border-[#00e5ff22]">
          <div className="text-xs text-[#00e5ffcc] mb-1 uppercase tracking-tighter font-bold">Current Balance</div>
          <div className="text-2xl font-mono text-white">₱1,402.50</div>
          <div className="flex justify-between items-center mt-3">
            <span className="text-[10px] text-gray-500">Min. Payout ₱100</span>
            <button className="text-[10px] text-[#2ecc71] font-bold uppercase">Withdraw</button>
          </div>
        </div>
      </aside>

      {/* MAIN CONTENT AREA */}
      <main className="flex-1 flex flex-col relative overflow-hidden">
        {/* Glow Background */}
        <div className="absolute top-0 right-0 w-[400px] h-[400px] bg-[#00e5ff0a] rounded-full blur-[120px] -z-10 pointer-events-none"></div>

        {/* HEADER */}
        <header className="h-20 border-b border-[#ffffff0a] px-8 flex items-center justify-between backdrop-blur-md z-10 shrink-0">
          <div className="relative w-96">
            <input 
              type="text" 
              placeholder="Search secure assets..." 
              className="w-full bg-[#111318] border border-[#ffffff11] rounded-full py-2 px-10 text-sm focus:outline-none focus:border-[#00e5ff] placeholder-gray-600 transition-colors"
            />
            <Search className="w-4 h-4 absolute left-4 top-2.5 text-gray-600" />
          </div>
          
          <div className="flex items-center gap-6">
            <div className="flex flex-col items-end">
              <span className="text-xs text-gray-500 uppercase font-semibold">Status</span>
              <span className="text-[10px] flex items-center gap-1.5 text-[#2ecc71]">
                <span className="w-1.5 h-1.5 bg-[#2ecc71] rounded-full animate-pulse"></span> 
                Secure Node
              </span>
            </div>
            <div className="flex items-center gap-3 pl-6 border-l border-[#ffffff11]">
              <div className="w-10 h-10 rounded-full bg-gradient-to-tr from-[#00e5ff] to-[#2ecc71] p-[2px] shadow-[0_0_10px_rgba(0,229,255,0.2)]">
                <div className="w-full h-full rounded-full bg-[#0a0c10] flex items-center justify-center text-xs font-bold text-white uppercase">
                  JD
                </div>
              </div>
            </div>
          </div>
        </header>

        {/* SYSTEM NOTICE BANNER */}
        <div className="bg-[#2ecc71]/10 border-b border-[#2ecc71]/30 p-2 text-center text-xs font-medium text-[#2ecc71] flex items-center justify-center gap-2 shrink-0">
          <Code size={14} />
          <span>PHP & SQL Source Files generated in <strong>/pinodrop</strong>. This is a visual preview.</span>
        </div>

        {/* SCROLLABLE CONTENT */}
        <section className="p-8 flex-1 overflow-y-auto">
          {view === 'feed' && <StoreFeed setView={setView} />}
          {view === 'verify' && <VerifyGate />}
          {view === 'dashboard' && <CreatorDashboard />}
        </section>

        {/* FOOTER */}
        <footer className="h-12 border-t border-[#ffffff0a] px-8 flex items-center justify-between bg-[#0a0c10] shrink-0">
          <div className="flex items-center gap-4">
            <span className="text-[10px] text-gray-600 font-bold uppercase tracking-widest">Network Speed: 85 Mbps</span>
            <span className="w-1 h-1 bg-gray-600 rounded-full"></span>
            <span className="text-[10px] text-[#00e5ff] font-bold uppercase tracking-widest">Ad-Gate Active: Step 1/2</span>
          </div>
          <div className="text-[10px] text-gray-600">
            © 2024 PinoDrop Protocol v3.0.1
          </div>
        </footer>
      </main>
    </div>
  );
}

function StoreFeed({ setView }: { setView: (v: any) => void }) {
  const categories = ["All", "Android APK", "Documents", "Tools"];
  const [activeCat, setActiveCat] = useState("All");

  const dummyFiles = [
    { id: 1, title: "CyberSafe VPN v4.2 [Mod]", desc: "Unlocked premium features, zero latency server selection, and anti-ban injection.", cat: "Android APK", size: "42.8 MB", dl: "12.4k", icon: "📱", tag: "PREMIUM", tagColor: "bg-[#00e5ff]" },
    { id: 2, title: "Board Exam Reviewer 2024", desc: "Complete compiled reviewers for civil engineering students. High pass rate materials.", cat: "Documents", size: "15.2 MB", dl: "8.1k", icon: "📄", tag: "VERIFIED", tagColor: "bg-[#2ecc71]" },
    { id: 3, title: "Auto-Clicker Pro Script", desc: "Precision auto-clicking utility with custom delay and coordinate patterns.", cat: "Tools", size: "2.1 MB", dl: "35.9k", icon: "🛠️", tag: "", tagColor: "" },
  ];

  return (
    <>
      <div className="flex items-center justify-between mb-8">
        <h2 className="text-2xl font-bold tracking-tight text-white flex items-center gap-3">
          <span className="w-2 h-8 bg-[#00e5ff] rounded-full"></span>
          Top Performing Assets
        </h2>
        <div className="flex gap-2">
          {categories.map(cat => (
            <button 
              key={cat}
              onClick={() => setActiveCat(cat)}
              className={`px-4 py-1.5 text-xs rounded-full font-medium transition-colors ${activeCat === cat ? 'bg-[#00e5ff11] border border-[#00e5ff33] text-[#00e5ff]' : 'bg-transparent border border-[#ffffff11] text-gray-500 hover:text-white'}`}
            >
              {cat}
            </button>
          ))}
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {dummyFiles.filter(f => activeCat === 'All' || f.cat === activeCat).map(file => (
          <div key={file.id} className="bg-[#0f1116] border border-[#ffffff0a] rounded-2xl p-5 hover:border-[#00e5ff44] transition-all group flex flex-col cursor-pointer" onClick={() => setView('verify')}>
            <div className="w-full h-32 bg-[#1a1d24] rounded-xl mb-4 relative overflow-hidden flex items-center justify-center">
              <span className="text-[40px] opacity-20 group-hover:scale-110 transition-transform">{file.icon}</span>
              {file.tag && (
                <div className={`absolute top-3 right-3 ${file.tagColor} text-black text-[10px] font-black px-2 py-0.5 rounded italic shadow-[0_0_8px_rgba(0,229,255,0.4)]`}>
                  {file.tag}
                </div>
              )}
            </div>
            
            <h3 className="text-white font-bold text-lg mb-1 leading-tight">{file.title}</h3>
            <p className="text-gray-500 text-xs mb-4 line-clamp-2 flex-grow">{file.desc}</p>
            
            <div className="flex items-center justify-between pt-4 border-t border-[#ffffff0a]">
              <div className="flex flex-col">
                <span className="text-[10px] text-gray-600 uppercase font-bold tracking-widest">Size</span>
                <span className="text-sm text-gray-300 font-mono">{file.size}</span>
              </div>
              <div className="flex flex-col items-end">
                <span className="text-[10px] text-gray-600 uppercase font-bold tracking-widest">Downloads</span>
                <span className="text-sm text-[#00e5ff] font-mono">{file.dl}</span>
              </div>
            </div>
          </div>
        ))}
      </div>
    </>
  );
}

function VerifyGate() {
  const [step, setStep] = useState(1);
  const [timeLeft, setTimeLeft] = useState(30);

  useEffect(() => {
    let interval: any;
    if (step === 2) {
      interval = setInterval(() => {
        setTimeLeft(prev => {
          if (prev <= 1) {
            clearInterval(interval);
            setStep(3);
            return 0;
          }
          return prev - 1;
        });
      }, 1000);
    }
    return () => clearInterval(interval);
  }, [step]);

  const handleSponsorClick = () => {
    setTimeout(() => {
      setStep(2);
    }, 1500); 
  };

  return (
    <div className="flex flex-col items-center justify-center py-10 h-full">
      <div className="relative w-full max-w-lg">
        <div className="relative z-10 bg-[#0f1116] border border-[#ffffff0a] p-10 rounded-2xl shadow-[0_0_30px_rgba(0,229,255,0.05)] text-center">
          
          {step === 1 && (
            <div className="animate-in fade-in zoom-in duration-500">
              <ShieldAlert size={56} className="mx-auto text-[#00e5ff] mb-6" />
              <h2 className="text-xl font-bold text-white uppercase tracking-tighter mb-4">Security <span className="text-[#00e5ff]">Check</span></h2>
              <p className="text-gray-500 text-sm mb-8">To verify you are human and support the creator, please visit our sponsor.</p>
              
              <button onClick={handleSponsorClick} className="w-full py-4 bg-[#00e5ff11] border border-[#00e5ff33] text-[#00e5ff] font-bold rounded-xl uppercase tracking-widest text-xs hover:bg-[#00e5ff] hover:text-black hover:shadow-[0_0_20px_rgba(0,229,255,0.4)] transition-all">
                Step 1: Visit Sponsor Ad
              </button>
            </div>
          )}

          {step === 2 && (
            <div className="animate-in fade-in duration-500">
              <Clock size={56} className="mx-auto text-[#00e5ff] mb-6 animate-pulse" />
              <h2 className="text-xl font-bold text-white uppercase tracking-tighter mb-4">Verifying<span className="text-[#00e5ff]">...</span></h2>
              <p className="text-gray-500 text-sm mb-6">Please wait while we prepare your secure download.</p>
              
              <div className="w-full h-2 bg-[#1a1d24] rounded-full overflow-hidden mb-6 border border-[#ffffff0a]">
                <div className="h-full bg-gradient-to-r from-[#00e5ff] to-[#2ecc71] transition-all duration-1000 ease-linear shadow-[0_0_10px_rgba(0,229,255,0.5)]" style={{ width: `${((30 - timeLeft) / 30) * 100}%` }}></div>
              </div>
              
              <div className="text-4xl font-mono text-white mb-8 font-light">{timeLeft}</div>

              <div className="w-full h-24 bg-[#1a1d24] border border-[#ffffff0a] rounded-xl flex items-center justify-center text-gray-600 text-xs font-medium uppercase tracking-widest overflow-hidden relative">
                 <div className="absolute inset-0 bg-[linear-gradient(45deg,transparent_25%,rgba(255,255,255,0.02)_50%,transparent_75%)] bg-[length:250%_250%,100%_100%] animate-[shimmer_2s_infinite]"></div>
                 Adsterra Banner Space
              </div>
            </div>
          )}

          {step === 3 && (
            <div className="animate-in fade-in zoom-in duration-500">
              <ShieldCheck size={56} className="mx-auto text-[#2ecc71] mb-6" />
              <h2 className="text-xl font-bold text-white uppercase tracking-tighter mb-4">Download <span className="text-[#2ecc71]">Ready</span></h2>
              <p className="text-gray-500 text-sm mb-8">Verification complete. Your file has been unlocked and the uploader credited.</p>
              
              <button className="w-full py-4 bg-[#2ecc71]/10 border border-[#2ecc71]/30 text-[#2ecc71] font-bold rounded-xl uppercase tracking-widest text-xs hover:bg-[#2ecc71] hover:text-black hover:shadow-[0_0_20px_rgba(46,204,113,0.4)] transition-all">
                Step 2: Direct Download Now
              </button>
            </div>
          )}
          
        </div>
      </div>
    </div>
  );
}

function CreatorDashboard() {
  return (
    <>
      <div className="flex items-center justify-between mb-8">
        <h2 className="text-2xl font-bold tracking-tight text-white flex items-center gap-3">
          <span className="w-2 h-8 bg-[#00e5ff] rounded-full"></span>
          Creator Hub
        </h2>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Sidebar */}
        <div className="space-y-6">
          <div className="bg-[#0f1116] border border-[#ffffff0a] rounded-2xl p-6">
            <h2 className="text-xs uppercase tracking-widest text-gray-500 font-bold flex items-center gap-2 mb-4"><Wallet size={14}/> Wallet Balance</h2>
            <div className="text-4xl font-mono text-[#2ecc71] mb-2 font-light">₱1,402.50</div>
            <p className="text-gray-600 text-[10px] mb-6 uppercase tracking-widest font-bold">Min. Payout ₱100</p>
            
            <hr className="border-[#ffffff0a] mb-6" />
            
            <h3 className="text-xs uppercase tracking-widest text-gray-300 font-bold mb-4">Request Cashout</h3>
            <div className="space-y-4">
              <div>
                <label className="block text-[10px] uppercase tracking-widest text-gray-500 font-bold mb-1.5">Amount (PHP)</label>
                <input type="number" defaultValue="500" className="w-full bg-[#1a1d24] border border-[#ffffff11] rounded-lg px-4 py-2.5 text-sm text-white focus:outline-none focus:border-[#00e5ff] transition-colors" />
              </div>
              <div>
                <label className="block text-[10px] uppercase tracking-widest text-gray-500 font-bold mb-1.5">Method</label>
                <select className="w-full bg-[#1a1d24] border border-[#ffffff11] rounded-lg px-4 py-2.5 text-sm text-white focus:outline-none focus:border-[#00e5ff] transition-colors appearance-none">
                  <option>GCash</option>
                  <option>PayPal</option>
                </select>
              </div>
              <button className="w-full bg-[#00e5ff11] border border-[#00e5ff33] text-[#00e5ff] hover:bg-[#00e5ff] hover:text-black hover:shadow-[0_0_15px_rgba(0,229,255,0.4)] transition-all font-bold py-3 rounded-lg mt-2 text-xs uppercase tracking-widest">
                Withdraw Funds
              </button>
            </div>
          </div>
        </div>

        {/* Main Content */}
        <div className="lg:col-span-2 space-y-6">
          <div className="bg-[#0f1116] border border-[#ffffff0a] rounded-2xl p-6">
            <h2 className="text-xs uppercase tracking-widest text-gray-500 font-bold flex items-center gap-2 mb-6"><Upload size={14} /> Upload New Asset</h2>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
              <div className="space-y-1.5">
                <label className="block text-[10px] uppercase tracking-widest text-gray-500 font-bold">Title</label>
                <input type="text" className="w-full bg-[#1a1d24] border border-[#ffffff11] rounded-lg px-4 py-2.5 text-sm focus:border-[#00e5ff] outline-none transition-colors" />
              </div>
              <div className="space-y-1.5">
                <label className="block text-[10px] uppercase tracking-widest text-gray-500 font-bold">Category</label>
                <select className="w-full bg-[#1a1d24] border border-[#ffffff11] rounded-lg px-4 py-2.5 text-sm focus:border-[#00e5ff] outline-none transition-colors appearance-none">
                  <option>Android APKs</option>
                  <option>Reviewers</option>
                  <option>Tools & Software</option>
                </select>
              </div>
              <div className="space-y-1.5">
                <label className="block text-[10px] uppercase tracking-widest text-gray-500 font-bold">File Size</label>
                <input type="text" placeholder="e.g. 15MB" className="w-full bg-[#1a1d24] border border-[#ffffff11] rounded-lg px-4 py-2.5 text-sm focus:border-[#00e5ff] outline-none transition-colors" />
              </div>
              <div className="space-y-1.5">
                <label className="block text-[10px] uppercase tracking-widest text-gray-500 font-bold">Download URL</label>
                <input type="url" placeholder="Drive / Mediafire Link" className="w-full bg-[#1a1d24] border border-[#ffffff11] rounded-lg px-4 py-2.5 text-sm focus:border-[#00e5ff] outline-none transition-colors" />
              </div>
              <div className="space-y-1.5 md:col-span-2">
                <label className="block text-[10px] uppercase tracking-widest text-gray-500 font-bold">Description</label>
                <textarea rows={3} className="w-full bg-[#1a1d24] border border-[#ffffff11] rounded-lg px-4 py-2.5 text-sm focus:border-[#00e5ff] outline-none transition-colors resize-none"></textarea>
              </div>
              <div className="md:col-span-2 mt-2">
                <button className="w-full bg-transparent border border-[#2ecc71] text-[#2ecc71] font-bold py-3.5 rounded-lg hover:bg-[#2ecc71] hover:text-black hover:shadow-[0_0_15px_rgba(46,204,113,0.4)] transition-all text-xs uppercase tracking-widest">
                  Publish Asset
                </button>
              </div>
            </div>
          </div>

          <div className="bg-[#0f1116] border border-[#ffffff0a] rounded-2xl p-6">
            <h2 className="text-xs uppercase tracking-widest text-gray-500 font-bold flex items-center gap-2 mb-6"><FileText size={14} /> My Uploads</h2>
            <div className="overflow-x-auto">
              <table className="w-full text-left border-collapse">
                <thead>
                  <tr className="border-b border-[#ffffff0a]">
                    <th className="pb-3 text-[10px] uppercase tracking-widest text-gray-500 font-bold">Title</th>
                    <th className="pb-3 text-[10px] uppercase tracking-widest text-gray-500 font-bold">Category</th>
                    <th className="pb-3 text-[10px] uppercase tracking-widest text-gray-500 font-bold text-right">Downloads</th>
                    <th className="pb-3 text-[10px] uppercase tracking-widest text-gray-500 font-bold text-right">Earnings</th>
                  </tr>
                </thead>
                <tbody className="text-sm">
                  <tr className="border-b border-[#ffffff0a] hover:bg-[#1a1d24] transition-colors">
                    <td className="py-4 font-medium text-white">GTA V Mobile Mod</td>
                    <td className="py-4 text-gray-500">Android APKs</td>
                    <td className="py-4 text-gray-300 font-mono text-right">14,203</td>
                    <td className="py-4 text-[#2ecc71] font-mono text-right font-medium">₱2,130.45</td>
                  </tr>
                  <tr className="hover:bg-[#1a1d24] transition-colors">
                    <td className="py-4 font-medium text-white">Adobe CC 2024</td>
                    <td className="py-4 text-gray-500">Tools & Software</td>
                    <td className="py-4 text-gray-300 font-mono text-right">4,520</td>
                    <td className="py-4 text-[#2ecc71] font-mono text-right font-medium">₱678.00</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </>
  );
}

